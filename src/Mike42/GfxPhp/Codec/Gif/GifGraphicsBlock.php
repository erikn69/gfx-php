<?php


namespace Mike42\GfxPhp\Codec\Gif;

use Mike42\GfxPhp\Codec\Common\DataInputStream;

class GifGraphicsBlock
{
    private $graphicControlExt;
    private $tableBasedImage;
    private $plaintextExt;

    public function __construct(GifGraphicControlExt $graphicControlExt = null, GifTableBasedImage $tableBasedImage = null, GifPlaintextExt $plaintextExt = null)
    {
        $this->graphicControlExt = $graphicControlExt;
        $this->tableBasedImage = $tableBasedImage;
        $this->plaintextExt = $plaintextExt;
    }

    public function getGraphicControlExt()
    {
        return $this->graphicControlExt;
    }

    public function getTableBasedImage()
    {
        return $this->tableBasedImage;
    }

    public function getPlaintextExt()
    {
        return $this->plaintextExt;
    }

    public static function fromBin(DataInputStream $in) : GifGraphicsBlock
    {
        $peek = $in -> peek(2);
        $blockId = $peek[0];
        $extensionId = $peek[1];
        // Could have a graphic control extension before it
        $graphicControlExtension = null;
        if ($blockId == GifData::GIF_EXTENSION && $extensionId == GifData::GIF_EXTENSION_GRAPHIC_CONTROL) {
            // Optional graphic control extension
            $graphicControlExtension = GifGraphicControlExt::fromBin($in);
            // Re-populate for next block
            $peek = $in -> peek(2);
            $blockId = $peek[0];
            $extensionId = $peek[1];
        }
        if ($blockId == GifData::GIF_EXTENSION && $extensionId == GifData::GIF_EXTENSION_PLAINTEXT) {
            // Plain text
            $plaintextExtension = GifPlaintextExt::fromBin($in);
            return new GifGraphicsBlock($graphicControlExtension, null, $plaintextExtension);
        } else if ($blockId == GifData::GIF_IMAGE_SEPARATOR) {
            // Table-based image
            $tableBasedImage = GifTableBasedImage::fromBin($in);
            return new GifGraphicsBlock($graphicControlExtension, $tableBasedImage, null);
        }
        throw new \Exception("Could not recognise a graphics or extension block; GIF file is corrupt");
    }
}
