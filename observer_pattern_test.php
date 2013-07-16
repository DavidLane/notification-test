<?php

class NotificationPlugin implements SplSubject {
  private $_observers = array();
	private $_payload = "";
  
  public function attach(SplObserver $observer) {
    $this->_observers[] = $observer;
  }
  
  public function detach(SplObserver $observer) {
    if ($key = array_search($observer, $this->_observers, true)) {
      unset($this->_observers[$key]);
    }
  }
  
  public function notify() {
    foreach($this->_observers as $observer) {
      $observer->update($this);
    }
  }

	public function getPayload() {
		return $this->_payload;
	}
	
	public function setPayload($payload) {
		$this->_payload = $payload;
		$this->notify();
	}
}

class Notification {
	private $_identifier;
	
	public function __construct($identifier) {
		$this->_identifier = $identifier;
	}
	
	public function getIdentifier() {
		return $this->_identifier;
	}
}

interface NotificationListener extends SplObserver {
	public function registerFor(Notification $notification);
	public function registeredFor(Notification $notification);
}

class EmailNotificationListener implements NotificationListener {
	private $_notification;
	
	public function update(SplSubject $subject) {
		if ($this->registeredFor($subject->getPayload())) {
			echo "Email Subscriber: ".$subject->getPayload()->getIdentifier()."\n";			
		}
	}
	
	public function registerFor(Notification $notification) {
		$this->_notification = $notification;
	}
	
	public function registeredFor(Notification $notification) {
		return $this->_notification == $notification;
	}
}

class PushNotificationListener implements NotificationListener {
	private $_notification;
		
	public function update(SplSubject $subject) {
		if ($this->registeredFor($subject->getPayload())) {
			echo "Push Subscriber: ".$subject->getPayload()->getIdentifier()."\n";			
		}
	}
	
	public function registerFor(Notification $notification) {
		$this->_notification = $notification;
	}
	
	public function registeredFor(Notification $notification) {
		return $this->_notification == $notification;
	}	
}

$email_notification = new Notification("Only Email subscribers should get this");
$push_notification = new Notification("Only Push subscribers should get this");

$email = new EmailNotificationListener();
$email->registerFor($email_notification);

$push = new PushNotificationListener();
$push->registerFor($push_notification);

$notification_plugin = new NotificationPlugin();
$notification_plugin->attach($email);
$notification_plugin->attach($push);

$notification_plugin->setPayload($email_notification);
$notification_plugin->setPayload($push_notification);

