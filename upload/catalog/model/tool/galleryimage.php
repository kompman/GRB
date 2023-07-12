<?php
class ModelToolGalleryimage extends Model { 
	public function resize($filename, $width, $height, $watermark = false, $crop = '') {
		if (!is_file(DIR_IMAGE . $filename)) {
			if (is_file(DIR_IMAGE . 'no_image.jpg')) {
				$filename = 'no_image.jpg';
			} elseif (is_file(DIR_IMAGE . 'no_image.png')) {
				$filename = 'no_image.png';
			} else {
				return;
			}
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$old_image = $filename;
		$new_image = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . $crop . '.' . $extension;

		if (!is_file(DIR_IMAGE . $new_image) || (filectime(DIR_IMAGE . $old_image) > filectime(DIR_IMAGE . $new_image))) {
			$path = '';

			$directories = explode('/', dirname(str_replace('../', '', $new_image)));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);

        $image = new Image(DIR_IMAGE . $old_image);
        
        $prop_orig = $width_orig / $height_orig;
        $prop_new = $width / $height; 
        
        if($crop == 'HA'){
          $height = $height_orig / $width_orig * $width;
        } else {
          if ($prop_orig > $prop_new) {
            $bottom_x = $height_orig * $prop_new;
            $bottom_y = $height_orig;
            $top_x = ($width_orig - $bottom_x) / 2;
            $top_y = 0;
          } else {
            $bottom_x = $width_orig;
            $bottom_y = $width_orig / $prop_new;
            $top_x = 0;
            $top_y = ($height_orig - $bottom_y) / 2;
          }
          $image->crop($top_x, $top_y, $bottom_x + $top_x, $bottom_y + $top_y);
        }

        $image->resize($width, $height);
        if ($watermark) {
          $image->watermark(DIR_IMAGE . 'watermark.png', 'custom');        
        }
				$image->save(DIR_IMAGE . $new_image);
		}

		$imagepath_parts = explode('/', $new_image);
		$new_image = implode('/', array_map('rawurlencode', $imagepath_parts));
		
		if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $new_image;
		} else {
			return $this->config->get('config_url') . 'image/' . $new_image;
		}
	}
}
