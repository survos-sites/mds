
Markdown for RecordWorkflowInterface

![RecordWorkflowInterface.svg](RecordWorkflowInterface.svg)



---
## Transition: process

### process.Transition

        onTransition()
        // 
```php
#[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_PROCESS)]
public function onTransition(TransitionEvent $event): void
{
    $record = $this->getRecord($event);
    dd($record);
    switch ($event->getTransition()->getName()) {
        case self::TRANSITION_PROCESS:
            break;
    }
}
```
[View source](mds/blob/main/src/Workflow/RecordWorkflow.php#L39-L47)


