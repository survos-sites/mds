Markdown for GrpWorkflow

![GrpWorkflow.svg](GrpWorkflow.svg)



## extract -- transition


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
blob/main/src/Workflow/GrpWorkflow.php#L46-62
        

## get_api_key -- transition


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
blob/main/src/Workflow/GrpWorkflow.php#L65-82
        
