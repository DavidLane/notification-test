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
	
	public function setPayload(Notification $payload) {
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
	public function isRegisteredFor(Notification $notification);
}

abstract class NotificationListenerConcrete implements NotificationListener {
	private $_notifications;
	
	public function registerFor(Notification $notification) {
		$this->_notifications[] = $notification;
	}
	
	public function isRegisteredFor(Notification $notification) {
		return in_array($notification, $this->_notifications);
	}	
}

class EmailNotificationListener extends NotificationListenerConcrete {
	private $_notification;
	
	public function update(SplSubject $subject) {
		if ($this->isRegisteredFor($subject->getPayload())) {
			echo "Email Subscriber: ".$subject->getPayload()->getIdentifier()."\n";			
		}
	}
}

class PushNotificationListener extends NotificationListenerConcrete {
	private $_notification;
		
	public function update(SplSubject $subject) {
		if ($this->isRegisteredFor($subject->getPayload())) {
			echo "Push Subscriber: ".$subject->getPayload()->getIdentifier()."\n";			
		}
	}
}

class EmailAndPushNotificationListener extends NotificationListenerConcrete {
	private $_notification;
		
	public function update(SplSubject $subject) {
		if ($this->isRegisteredFor($subject->getPayload())) {
			echo "EmailAndPush Subscriber: ".$subject->getPayload()->getIdentifier()."\n";			
		}
	}
}

$email_notification = new Notification("Only Email subscribers should get this");
$push_notification = new Notification("Only Push subscribers should get this");

$email = new EmailNotificationListener();
$email->registerFor($email_notification);

$push = new PushNotificationListener();
$push->registerFor($push_notification);

$emailandpush = new EmailAndPushNotificationListener();
$emailandpush->registerFor($email_notification);
$emailandpush->registerFor($push_notification);

$notification_plugin = new NotificationPlugin();
$notification_plugin->attach($email);
$notification_plugin->attach($push);
$notification_plugin->attach($emailandpush);

$notification_plugin->setPayload($email_notification);
$notification_plugin->setPayload($push_notification);

