<?php
	
	namespace Advitum\Cmd\Plugins;
	
	use \Advitum\Cmd\Plugin;
	use \Advitum\Cmd\Form;
	use \Advitum\Cmd\Router;
	use \Advitum\Cmd\Validator;
	use \Advitum\Cmd\Session;
	
	class Contact extends Plugin
	{
		public function frontend() {
			if(!isset($this->params['to']) || !isset($this->params['from'])) {
				return;
			}
			
			if(Form::sent('contactForm')) {
				if(Validator::validate('contactForm', array(
					'name' => array(
						'message' => 'Please enter your name.'
					),
					'email' => array(
						'message' => 'Please enter your E-Mail-Address.'
					),
					'message' => array(
						'message' => 'Please enter a message.'
					)
				))) {
					mail($this->params['to'], 'Someone has sent you a message.', 'Name: ' . Form::value('contactForm', 'name') . "\n" . 'E-Mail: ' . Form::value('contactForm', 'email') . "\n\n" . Form::value('contactForm', 'message'), implode("\n", array(
						'From:' . $this->params['from'],
						'Content-type: text/plain; charset=UTF-8'
					)));
					Session::setMessage('Your message has been sent. Thank you.', 'success');
					Router::redirect(Router::here());
				} else {
					Session::setMessage('Your message could not be sent. Please fill out all fields.', 'error');
				}
			}
			
			$html = '';
			
			$html .= Form::create(Router::here(), 'contactForm');
			
			$html .= Form::input('name', array(
				'label' => 'Your Name',
				'placeholder' => 'Your Name'
			));
			$html .= Form::input('email', array(
				'label' => 'Your E-Mail-Address',
				'placeholder' => 'Your E-Mail-Address'
			));
			$html .= Form::input('message', array(
				'label' => 'Your Message',
				'placeholder' => 'Your Message',
				'type' => 'textarea'
			));
			
			$html .= Form::end('Send');
			
			return $html;
		}
	}
	
?>