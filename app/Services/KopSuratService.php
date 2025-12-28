<?php

namespace App\Services;

use App\Models\SekolahSettings;
use Illuminate\Support\Facades\Storage;

class KopSuratService
{
    /**
     * Render kop surat HTML dari config
     * 
     * @param SekolahSettings|null $sekolah
     * @param bool $forPDF - true untuk PDF (base64 images), false untuk HTML preview
     * @return string
     */
    public function renderKopHtml($sekolah = null, $forPDF = true)
    {
        if (!$sekolah) {
            $sekolah = SekolahSettings::first();
        }

        if (!$sekolah) {
            return $this->renderFallbackKop();
        }

        // Jika mode custom, return custom image
        if ($sekolah->kop_mode === 'custom' && $sekolah->kop_surat_custom_path) {
            return $this->renderCustomKop($sekolah, $forPDF);
        }

        // Mode builder - render from config
        return $this->renderBuilderKop($sekolah, $forPDF);
    }

    /**
     * Render custom kop image
     */
    private function renderCustomKop($sekolah, $forPDF)
    {
        $imagePath = storage_path('app/public/' . $sekolah->kop_surat_custom_path);
        
        if ($forPDF && file_exists($imagePath)) {
            // Resize custom kop to reduce memory
            $resizedImage = $this->resizeImageForPDF($imagePath, 800, 300);
            if ($resizedImage) {
                $imageBase64 = $resizedImage;
            } else {
                // Fallback
                $imageData = base64_encode(file_get_contents($imagePath));
                $mimeType = mime_content_type($imagePath);
                $imageBase64 = "data:{$mimeType};base64,{$imageData}";
            }
        } else {
            $imageBase64 = asset('storage/' . $sekolah->kop_surat_custom_path);
        }

        return '<div style="text-align: center; margin-bottom: 10px;">
                    <img src="' . $imageBase64 . '" style="max-width: 100%; height: auto;">
                </div>';
    }

    /**
     * Render builder mode kop (3 kolom: Logo Kemenag | Text | Logo Sekolah)
     */
    private function renderBuilderKop($sekolah, $forPDF)
    {
        // Get logo paths
        $logoKemenagSrc = $this->getLogoSrc($sekolah->logo_kemenag_path, $forPDF);
        $logoSekolahSrc = $this->getLogoSrc($sekolah->logo, $forPDF);

        // Get logo heights
        $logoKemenagHeight = $sekolah->logo_display_height ?? 80;
        $logoSekolahHeight = $sekolah->logo_display_height ?? 80;

        // Build center content from config
        $centerContent = $this->buildCenterContent($sekolah);

        // Build HTML table structure with balanced positioning
        $html = '
        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
            <tr>
                <td width="12%" align="center" valign="middle" style="padding: 5px;">';
        
        if ($logoKemenagSrc) {
            $html .= '<img src="' . $logoKemenagSrc . '" alt="Logo Kemenag" style="height: ' . $logoKemenagHeight . 'px; display: block; margin: 0 auto;">';
        }
        
        $html .= '</td>
                <td width="76%" align="center" valign="middle" style="padding: 5px 15px;">' . $centerContent . '</td>
                <td width="12%" align="center" valign="middle" style="padding: 5px;">';
        
        if ($logoSekolahSrc) {
            $html .= '<img src="' . $logoSekolahSrc . '" alt="Logo Sekolah" style="height: ' . $logoSekolahHeight . 'px; display: block; margin: 0 auto;">';
        }
        
        $html .= '</td>
            </tr>
        </table>';

        // Add divider line with precise spacing
        $html .= '<div style="border-bottom: 3px double #000; margin: 5px 0 10px 0; clear: both;"></div>';

        return $html;
    }

    /**
     * Get logo source (base64 for PDF, URL for HTML)
     */
    private function getLogoSrc($logoPath, $forPDF)
    {
        if (!$logoPath) {
            return null;
        }

        if ($forPDF) {
            $fullPath = storage_path('app/public/' . $logoPath);
            if (file_exists($fullPath)) {
                // Resize image to reduce memory usage
                $resizedImage = $this->resizeImageForPDF($fullPath, 200, 200);
                if ($resizedImage) {
                    return $resizedImage;
                }
                
                // Fallback to original if resize fails
                $imageData = base64_encode(file_get_contents($fullPath));
                $mimeType = mime_content_type($fullPath);
                return "data:{$mimeType};base64,{$imageData}";
            }
        }

        return asset('storage/' . $logoPath);
    }

    /**
     * Resize image for PDF to reduce memory usage
     */
    private function resizeImageForPDF($filePath, $maxWidth = 200, $maxHeight = 200)
    {
        try {
            $imageInfo = getimagesize($filePath);
            if (!$imageInfo) {
                return null;
            }

            list($width, $height, $type) = $imageInfo;

            // Create image resource based on type
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = @imagecreatefromjpeg($filePath);
                    break;
                case IMAGETYPE_PNG:
                    $source = @imagecreatefrompng($filePath);
                    break;
                case IMAGETYPE_GIF:
                    $source = @imagecreatefromgif($filePath);
                    break;
                default:
                    return null;
            }

            if (!$source) {
                return null;
            }

            // Calculate new dimensions
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            if ($ratio >= 1) {
                // Image is already smaller, use original
                $newWidth = $width;
                $newHeight = $height;
            } else {
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);
            }

            // Create new image
            $destination = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG
            if ($type == IMAGETYPE_PNG) {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
                $transparent = imagecolorallocatealpha($destination, 0, 0, 0, 127);
                imagefill($destination, 0, 0, $transparent);
            }

            // Resize
            imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Capture output
            ob_start();
            switch ($type) {
                case IMAGETYPE_JPEG:
                    imagejpeg($destination, null, 85); // 85% quality
                    $mimeType = 'image/jpeg';
                    break;
                case IMAGETYPE_PNG:
                    imagepng($destination, null, 6); // Compression level 6
                    $mimeType = 'image/png';
                    break;
                case IMAGETYPE_GIF:
                    imagegif($destination);
                    $mimeType = 'image/gif';
                    break;
            }
            $imageData = ob_get_clean();

            // Free memory
            imagedestroy($source);
            imagedestroy($destination);

            return "data:{$mimeType};base64," . base64_encode($imageData);

        } catch (\Exception $e) {
            \Log::error('Image resize failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build center content from kop_surat_config
     */
    private function buildCenterContent($sekolah)
    {
        $config = $sekolah->kop_surat_config;
        
        if (!$config || !isset($config['elements']) || empty($config['elements'])) {
            // Default fallback
            return $this->buildDefaultCenterContent($sekolah);
        }

        $html = '';

        foreach ($config['elements'] as $element) {
            $type = $element['type'] ?? '';
            $content = $element['content'] ?? [];

            switch ($type) {
                case 'text':
                    $html .= $this->renderTextElement($content);
                    break;

                case 'divider':
                    $html .= $this->renderDividerElement($content);
                    break;

                case 'contact':
                    $html .= $this->renderContactElement($content);
                    break;
            }
        }

        return $html;
    }

    /**
     * Render text element
     */
    private function renderTextElement($content)
    {
        $fontSize = $content['fontSize'] ?? 12;
        $align = $content['align'] ?? 'center';
        $bold = $content['bold'] ?? true;
        $fontWeight = $bold ? 'bold' : 'normal';

        $html = '';
        $style = "text-align: {$align}; font-size: {$fontSize}pt; font-weight: {$fontWeight}; margin: 1px 0; padding: 0; line-height: 1.2;";

        if (!empty($content['line1'])) {
            $html .= '<div style="' . $style . '">' . htmlspecialchars($content['line1']) . '</div>';
        }
        if (!empty($content['line2'])) {
            $html .= '<div style="' . $style . '">' . htmlspecialchars($content['line2']) . '</div>';
        }
        if (!empty($content['line3'])) {
            $html .= '<div style="' . $style . '">' . htmlspecialchars($content['line3']) . '</div>';
        }

        return $html;
    }

    /**
     * Render divider element
     */
    private function renderDividerElement($content)
    {
        $style = $content['style'] ?? 'solid';
        $width = $content['width'] ?? 2;
        $color = $content['color'] ?? '#000000';
        $marginTop = $content['marginTop'] ?? 5;
        $marginBottom = $content['marginBottom'] ?? 5;

        return '<hr style="border: none; border-top: ' . $width . 'px ' . $style . ' ' . $color . '; margin: ' . $marginTop . 'px 0 ' . $marginBottom . 'px 0; padding: 0;">';
    }

    /**
     * Render contact element
     */
    private function renderContactElement($content)
    {
        $html = '<div style="font-size: 9pt; text-align: center; margin: 2px 0; padding: 0; line-height: 1.2;">';
        
        $parts = [];
        if (!empty($content['alamat'])) {
            $parts[] = htmlspecialchars($content['alamat']);
        }
        if (!empty($content['telepon'])) {
            $parts[] = 'Telp: ' . htmlspecialchars($content['telepon']);
        }
        if (!empty($content['email'])) {
            $parts[] = 'Email: ' . htmlspecialchars($content['email']);
        }
        if (!empty($content['website'])) {
            $parts[] = htmlspecialchars($content['website']);
        }

        $html .= implode(' | ', $parts);
        $html .= '</div>';

        return $html;
    }

    /**
     * Build default center content if no config exists
     */
    private function buildDefaultCenterContent($sekolah)
    {
        $html = '<div style="text-align: center; font-weight: bold; padding: 0; margin: 0;">';
        $html .= '<div style="font-size: 11pt; margin: 1px 0; padding: 0; line-height: 1.2;">KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>';
        $html .= '<div style="font-size: 14pt; margin: 1px 0; padding: 0; line-height: 1.2;">' . strtoupper($sekolah->nama_sekolah ?? 'SEKOLAH') . '</div>';
        
        if ($sekolah->alamat_jalan) {
            $html .= '<div style="font-size: 9pt; margin: 2px 0; padding: 0; font-weight: normal; line-height: 1.2;">';
            $html .= htmlspecialchars($sekolah->alamat_jalan);
            if ($sekolah->telepon) {
                $html .= ' | Telp: ' . htmlspecialchars($sekolah->telepon);
            }
            if ($sekolah->email) {
                $html .= ' | Email: ' . htmlspecialchars($sekolah->email);
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';

        return $html;
    }

    /**
     * Fallback kop if no sekolah settings found
     */
    private function renderFallbackKop()
    {
        return '<div style="text-align: center; padding: 10px; border-bottom: 2px solid #000;">
                    <h2 style="margin: 5px 0;">SEKOLAH</h2>
                    <p style="margin: 3px 0; font-size: 10pt;">Alamat Sekolah</p>
                </div>';
    }
}
