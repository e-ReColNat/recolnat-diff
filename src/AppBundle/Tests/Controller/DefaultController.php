<?php
namespace AppBundle\Tests\Controller;

use atoum\AtoumBundle\Test\Units\WebTestCase;
use atoum\AtoumBundle\Test\Controller\ControllerTest;

class DefaultController extends ControllerTest
{
    public function testIndex()
    {
        $this
            ->request(array('debug' => true))
                ->GET('/')
                    ->hasStatus(200)
                    ->hasCharset('UTF-8')
                    ->hasVersion('1.1')
                    ->crawler
                        ->hasElement('h1')
                            ->withContent('Welcome to Symfony')
                            ->exactly(0)
                        ->end()
                //$this->assertContains('Welcome to Symfony', $crawler->filter('#container h1')->text());
                /*->POST('/demo/contact')
                    ->hasStatus(200)
                    ->hasHeader('Content-Type', 'text/html; charset=UTF-8')
                    ->crawler
                        ->hasElement('#contact_form')
                            ->hasChild('input')->exactly(3)->end()
                            ->hasChild('input')
                                ->withAttribute('type', 'email')
                                ->withAttribute('name', 'contact[email]')
                            ->end()
                            ->hasChild('input[type=submit]')
                                ->withAttribute('value', 'Send')
                            ->end()
                            ->hasChild('textarea')->end()
                        ->end()
                        ->hasElement('li')
                            ->withContent('The CSRF token is invalid. Please try to resubmit the form.')
                            ->exactly(1)
                        ->end()
                        ->hasElement('title')
                            ->hasNoChild()
                        ->end()
                        ->hasElement('meta')
                            ->hasNoContent()
                        ->end()
                        ->hasElement('link')
                            ->isEmpty()
                        ->end()*/
        ;
    }
}