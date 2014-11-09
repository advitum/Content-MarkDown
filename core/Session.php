<?php
	
	namespace Advitum\Cmd;
	
	class Session
	{
		public static function setMessage($text, $class = false) {
			$_SESSION['message'] = array(
				'text' => $text,
				'class' => $class
			);
		}
		
		public static function printMessage() {
			if(isset($_SESSION['message']) && $_SESSION['message'] !== false) {
?><div id="message"<?php if($_SESSION['message']['class'] !== false) { echo ' class="' . htmlspecialchars($_SESSION['message']['class']) . '"'; } ?>>
	<?php echo $_SESSION['message']['text']; ?>
</div><?php
				
				$_SESSION['message'] = false;
			}
		}
	}
	
?>