<?php

declare(strict_types=1);

namespace App\Listener;

use AdminBundle\Events\UserLoginEvent;
use AdminBundle\Repositories\AdminRepository;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener 
 */
class UserLoginListener implements ListenerInterface 
{
    public function listen(): array
    {
        return [
            UserLoginEvent::class,
        ];
    }

    /**
     * @param UserLoginEvent $event
     */
    public function process(object $event)
    {
        if ($event instanceof UserLoginEvent) {
            $user = $event->user;
            
            if ($event->provider == 'admin') {
                AdminRepository::instance()->saveData($user);
            }
        }
    }
}
