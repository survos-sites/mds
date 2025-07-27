Markdown for SourceWorkflow

![SourceWorkflow.svg](SourceWorkflow.svg)



##  -- guard


```php
#[AsGuardListener(self::WORKFLOW_NAME)]
public function onGuard(GuardEvent $event): void
{
    // switch ($event->getTransition()) { ...
}
```
blob/main/src/Workflow/SourceWorkflow.php#L26-29
        


##  -- transition


```php
#[AsTransitionListener(self::WORKFLOW_NAME)]
public function onTransition(TransitionEvent $event): void
{
    switch ($event->getTransition()->getName()) {
        case self::TRANSITION_SCRAPE:
            break;
        case self::TRANSITION_PROCESS:
            break;
    }
}
```
blob/main/src/Workflow/SourceWorkflow.php#L32-40
        
