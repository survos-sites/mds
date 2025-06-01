<?php

namespace App\Tests\Crawl;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Survos\CrawlerBundle\Tests\BaseVisitLinksTest;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CrawlAsVisitorTest extends BaseVisitLinksTest
{
	#[TestDox('/$method $url ($route)')]
	#[TestWith(['', 'App\Entity\User', '/', 200])]
	#[TestWith(['', 'App\Entity\User', '/record', 200])]
	#[TestWith(['', 'App\Entity\User', '/source', 200])]
	#[TestWith(['', 'App\Entity\User', '/grp', 200])]
	#[TestWith(['', 'App\Entity\User', '/extract', 200])]
	#[TestWith(['', 'App\Entity\User', '/api', 200])]
	public function testRoute(string $username, string $userClassName, string $url, string|int|null $expected): void
	{
		parent::xxtestWithLogin($username, $userClassName, $url, (int)$expected);
	}
}
