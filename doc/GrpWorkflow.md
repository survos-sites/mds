
Markdown for GrpWorkflow

![GrpWorkflow.svg](GrpWorkflow.svg)



---
## Transition: get_api_key

### get_api_key.Transition

        onFetchApiKey()
        // fetch the initial API key
```php
#[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_API_KEY)]
public function onFetchApiKey(TransitionEvent $event): void
{
    $grp = $this->getGrp($event);
    if (!$grp->getStartToken()) {

        $apiKeyRequest = "https://museumdata.uk/get-api-token/get_api_token.php?user_id=tacman&institution=Museado&q=" .
            urlencode($grp->name);
        $apiKeyData = $this->cache->get($grp->id, fn(ItemInterface $item) => json_decode(file_get_contents($apiKeyRequest)));
        if (!$apiKeyData) {
            dd($apiKeyData, $apiKeyRequest);
        }
        $this->logger->warning($apiKeyRequest);
        $grp
            ->setCount($apiKeyData->count)
            ->setStartToken($apiKeyData->resume);
        $this->entityManager->flush(); // so that the next transition is accurate
    }
}
```
[View source](mds/blob/main/src/Workflow/GrpWorkflow.php#L65-L82)




---
## Transition: extract

### extract.Transition

        onDispatchExtract()
        // create the extract?
```php
#[AsTransitionListener(self::WORKFLOW_NAME, self::TRANSITION_EXTRACT)]
public function onDispatchExtract(TransitionEvent $event): void
{
    $grp = $this->getGrp($event);

    $token = $grp->getStartToken();
    $tokenCode = Extract::calcCode($token);

    if (!$extract = $this->extractRepository->findOneBy(['tokenCode' => $tokenCode])) {
        $extract = new Extract($token, $grp);
        assert($extract->getTokenCode() === $tokenCode);
        $this->entityManager->persist($extract);
    }
    $this->entityManager->flush();
    // dispatch the first extract
    $this->extractWorkflowClass->dispatchNextExtract($extract->getToken(), $extract);

}
```
[View source](mds/blob/main/src/Workflow/GrpWorkflow.php#L46-L62)


