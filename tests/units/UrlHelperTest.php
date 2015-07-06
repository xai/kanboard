<?php

require_once __DIR__.'/Base.php';

use Helper\Url;
use Model\Config;

class UrlHelperTest extends Base
{
    public function testLink()
    {
        $h = new Url($this->container);
        $this->assertEquals(
            '<a href="?controller=a&amp;action=b&amp;d=e" class="f" title="g" target="_blank">label</a>',
            $h->link('label', 'a', 'b', array('d' => 'e'), false, 'f', 'g', true)
        );
    }

    public function testHref()
    {
        $h = new Url($this->container);
        $this->assertEquals(
            '?controller=a&amp;action=b&amp;d=e',
            $h->href('a', 'b', array('d' => 'e'))
        );
    }

    public function testTo()
    {
        $h = new Url($this->container);
        $this->assertEquals(
            '?controller=a&action=b&d=e',
            $h->to('a', 'b', array('d' => 'e'))
        );
    }

    public function testServer()
    {
        $h = new Url($this->container);

        $this->assertEquals('http://localhost/', $h->server());

        $_SERVER['PHP_SELF'] = '/';
        $_SERVER['SERVER_NAME'] = 'kb';
        $_SERVER['SERVER_PORT'] = 1234;

        $this->assertEquals('http://kb:1234/', $h->server());
    }

    public function testBase()
    {
        $h = new Url($this->container);

        $this->assertEquals('http://localhost/', $h->base());

        $_SERVER['PHP_SELF'] = '/';
        $_SERVER['SERVER_NAME'] = 'kb';
        $_SERVER['SERVER_PORT'] = 1234;

        $this->assertEquals('http://kb:1234/', $h->base());

        $c = new Config($this->container);
        $c->save(array('application_url' => 'https://mykanboard/'));

        $this->assertEquals('https://mykanboard/', $c->get('application_url'));
        $this->assertEquals('https://mykanboard/', $h->base());
    }
}
