Markdown for MuseumObjectWorkflow

![MuseumObjectWorkflow.svg](MuseumObjectWorkflow.svg)



##  -- guard


```php
#[AsGuardListener(self::WORKFLOW_NAME)]
public function onGuard(GuardEvent $event): void
{
    $museumObject = $this->getMuseumObject($event);

    switch ($event->getTransition()->getName()) {
    /*
    e.g.
    if ($event->getSubject()->cannotTransition()) {
      $event->setBlocked(true, "reason");
    }
    App\Entity\MuseumObject
    */
        case self::TRANSITION_THUMBNAILS:
            break;
        case self::TRANSITION_FINISH:
            break;
    }
}
```
blob/main/src/Workflow/MuseumObjectWorkflow.php#L29-46
        


## finish -- transition


```php
#[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_FINISH)]
public function onFinish(TransitionEvent $event): void
{
    $museumObject = $this->getMuseumObject($event);
}
```
blob/main/src/Workflow/MuseumObjectWorkflow.php#L57-60
        

## thumbnails -- transition


```php
#[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_THUMBNAILS)]
public function onThumbnails(TransitionEvent $event): void
{
    $museumObject = $this->getMuseumObject($event);
}
```
blob/main/src/Workflow/MuseumObjectWorkflow.php#L50-53
        
