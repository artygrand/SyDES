<?php
class SimpleImage{
	public $image;
	public $image_type;
	public $width;
	public $height;

	public function load($filename){
		$image_info = @getimagesize($filename);
		if (!$image_info or !in_array($image_info[2], array(1,2,3))){
			header("HTTP/1.0 404 Not Found");
			die;
		}

		$this->image_type = $image_info[2];
		if($this->image_type == IMAGETYPE_JPEG ){
			$this->image = imagecreatefromjpeg($filename);
		} elseif( $this->image_type == IMAGETYPE_GIF ){
			$this->image = imagecreatefromgif($filename);
		} elseif( $this->image_type == IMAGETYPE_PNG ){
			$this->image = imagecreatefrompng($filename);
		}
		
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
	}

	public function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=0777) {
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image,$filename,$compression);
		} elseif( $image_type == IMAGETYPE_GIF ) {
			imagegif($this->image,$filename);
		} elseif( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image,$filename);
		}
		if( $permissions != null) {
			chmod($filename,$permissions);
		}
	}

	public function resize($thumb_width, $thumb_height){
		if($thumb_width / $this->width < $thumb_height / $this->height){
			$new_width = $thumb_width;
			$new_height = $this->height * ($thumb_width / $this->width);
		} else {
			$new_width = $this->width * ($thumb_height / $this->height);
			$new_height = $thumb_height;
		}
		$new_image = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
		$this->image = $new_image;
	}

	public function crop($thumb_width, $thumb_height){
		if ($this->width / $this->height >= $thumb_width / $thumb_height){
			$new_width = $this->width / ($this->height / $thumb_height);
			$new_height = $thumb_height;
		} else {
			$new_width = $thumb_width;
			$new_height = $this->height / ($this->width / $thumb_width);
		}
		$new_image = imagecreatetruecolor($thumb_width, $thumb_height);
		imagecopyresampled($new_image, $this->image, 0 - ($new_width - $thumb_width) / 2, 0 - ($new_height - $thumb_height) / 2, 0, 0, $new_width, $new_height, $this->width, $this->height);
		$this->image = $new_image;
	}

	public function doit($what, $width, $height){
		$this -> $what($width, $height);
	}

	public function destroy() {
		imagedestroy($this->image);
	}
}

if (isset($_GET['src']) and $_GET['src']){
	$pic_str = str_replace('/', '_', $_GET['src']);
	$pic_width = (int)$_GET['width'];
	$pic_height = (int)$_GET['height'];
	$cachedImg = 'cache/pic_' . $pic_width . '_' . $pic_height . '_' . $pic_str;
	if (!is_file($cachedImg)){
		$image = new SimpleImage();	
		$image->load($_GET['src']);
		$image->doit('crop', $pic_width, $pic_height);
		$image->save($cachedImg);
		$image->destroy();
	}
	header('Location: /' . $cachedImg);
	die;
}
?>