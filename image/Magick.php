<?php

namespace ivanchkv\kladovka\image;


class Magick
{

    public static function init($data = null)
    {
        return new self($data);
    }

    public function __construct($data = null)
    {
        if ($data) {
            if (is_string($data)) {
                $this->setInputFilename($data);
            } elseif (is_array($data)) {
                foreach ($data as $key => $value) {
                    if (is_string($key)) {
                        $methodName = 'set' . ucfirst($key);
                        if (method_exists($this, $methodName)) {
                            $this->{$methodName}($value);
                        }
                    }
                }
            }
        }
    }

    private $_inputFilename = null;

    public function setInputFilename($inputFilename)
    {
        $this->_inputFilename = $inputFilename;
        return $this;
    }

    public function getInputFilename()
    {
        return $this->_inputFilename;
    }

    public function inputFilename($inputFilename = null)
    {
        if (!is_null($inputFilename)) {
            return $this->setInputFilename($inputFilename);
        } else {
            return $this->getInputFilename();
        }
    }

    private $_width = null;

    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    public function width($width = null)
    {
        if (!is_null($width)) {
            return $this->setWidth($width);
        } else {
            return $this->getWidth();
        }
    }

    private $_height = null;

    public function setHeight($height)
    {
        $this->_height = $height;
        return $this;
    }

    public function getHeight()
    {
        return $this->_height;
    }

    public function height($height = null)
    {
        if (!is_null($height)) {
            return $this->setHeight($height);
        } else {
            return $this->getHeight();
        }
    }

    private $_size = null;

    public function setSize($size)
    {
        $this->_size = $size;
        if ($size && is_string($size)) {
            $size = explode('x', $size, 2);
        }
        if ($size && is_array($size)) {
            $this->setWidth(array_key_exists(0, $size) ? (int)$size[0] : (array_key_exists('width', $size) ? (int)$size['width'] : null));
            $this->setHeight(array_key_exists(1, $size) ? (int)$size[1] : (array_key_exists('height', $size) ? (int)$size['height'] : null));
        } else {
            $this->setWidth(null);
            $this->setHeight(null);
        }
        return $this;
    }

    public function getSize()
    {
        return $this->_size;
    }

    public function size($size = null)
    {
        if (!is_null($size)) {
            return $this->setSize($size);
        } else {
            return $this->getSize();
        }
    }

    protected function buildSize()
    {
        $width = $this->getWidth();
        $height = $this->getHeight();
        if ($width && $height && is_int($width) && is_int($height)) {
            return $width . 'x' . $height;
        }
        return false;
    }

    private $_thumbnail = null;

    public function setThumbnail($thumbnail)
    {
        $this->_thumbnail = $thumbnail;
        return $this;
    }

    public function getThumbnail()
    {
        return $this->_thumbnail;
    }

    public function thumbnail($thumbnail = null)
    {
        if (!is_null($thumbnail)) {
            return $this->setThumbnail($thumbnail);
        } else {
            return $this->getThumbnail();
        }
    }

    private $_unsharp = null;

    public function setUnsharp($unsharp)
    {
        $this->_unsharp = $unsharp;
        return $this;
    }

    public function getUnsharp()
    {
        return $this->_unsharp;
    }

    public function unsharp($unsharp = null)
    {
        if (!is_null($unsharp)) {
            return $this->setUnsharp($unsharp);
        } else {
            return $this->getUnsharp();
        }
    }

    private $_gravity = null;

    public function setGravity($gravity)
    {
        $this->_gravity = $gravity;
        return $this;
    }

    public function getGravity()
    {
        return $this->_gravity;
    }

    public function gravity($gravity = null)
    {
        if (!is_null($gravity)) {
            return $this->setGravity($gravity);
        } else {
            return $this->getGravity();
        }
    }

    private $_crop = null;

    public function setCrop($crop)
    {
        $this->_crop = $crop;
        return $this;
    }

    public function getCrop()
    {
        return $this->_crop;
    }

    public function crop($crop = null)
    {
        if (!is_null($crop)) {
            return $this->setCrop($crop);
        } else {
            return $this->getCrop();
        }
    }

    private $_cropThumbnail = null;

    public function setCropThumbnail($cropThumbnail)
    {
        $this->_cropThumbnail = $cropThumbnail;
        if ($cropThumbnail && is_string($cropThumbnail)) {
            $this->setSize($cropThumbnail);
            $this->setThumbnail(true);
            $this->setUnsharp(true);
            $this->setGravity('north');
            $this->setCrop(true);
        }
        return $this;
    }

    public function getCropThumbnail()
    {
        return $this->_cropThumbnail;
    }

    public function cropThumbnail($cropThumbnail = null)
    {
        if (!is_null($cropThumbnail)) {
            return $this->setCropThumbnail($cropThumbnail);
        } else {
            return $this->getCropThumbnail();
        }
    }

    private $_outputFilename = null;

    public function setOutputFilename($outputFilename)
    {
        $this->_outputFilename = $outputFilename;
        return $this;
    }

    public function getOutputFilename()
    {
        return $this->_outputFilename;
    }

    public function outputFilename($outputFilename = null)
    {
        if (!is_null($outputFilename)) {
            return $this->setOutputFilename($outputFilename);
        } else {
            return $this->getOutputFilename();
        }
    }

    private $_args = null;

    public function setArgs($args)
    {
        $this->_args = $args;
        return $this;
    }

    public function getArgs()
    {
        return $this->_args;
    }

    public function args($args = null)
    {
        if (!is_null($args)) {
            return $this->setArgs($args);
        } else {
            return $this->getArgs();
        }
    }

    protected function buildArgs()
    {
        $args = $this->getArgs();
        if (!is_array($args)) {
            $args = [];
        }
        $inputFilename = $this->getInputFilename();
        if ($inputFilename && is_string($inputFilename)) {
            $args[] = $inputFilename;
        }
        $size = $this->buildSize();
        if (!$size) {
            $size = $this->getSize();
        }
        if ($size && is_string($size)) {
            $thumbnail = $this->getThumbnail();
            if ($thumbnail && is_bool($thumbnail)) {
                $args['thumbnail'] = $size . '^';
            }
            $unsharp = $this->getUnsharp();
            if ($unsharp) {
                if (is_bool($unsharp)) {
                    $args['unsharp'] = '0x.5';
                } elseif (is_string($unsharp)) {
                    $args['unsharp'] = $unsharp;
                }
            }
            $gravity = $this->getGravity();
            if ($gravity) {
                if (is_bool($gravity)) {
                    $args['gravity'] = 'center';
                } elseif (is_string($gravity)) {
                    $args['gravity'] = $gravity;
                }
            }
            $crop = $this->getCrop();
            if ($crop && is_bool($crop)) {
                $args['crop'] = $size . '+0+0';
            }
        }
        $outputFilename = $this->getOutputFilename();
        if ($outputFilename && is_string($outputFilename)) {
            $args[] = $outputFilename;
        }
        return $args;
    }
}
