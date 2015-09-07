<?php

require_once 'autoload.php';

class InflectorTest extends TipyTestCase {

    public function testClassify() {
        $this->assertEqual(TipyInflector::classify('welcome'), 'Welcome');
        $this->assertEqual(TipyInflector::classify('wELcOme'), 'WELcOme');
        $this->assertEqual(TipyInflector::classify('welcomes'), 'Welcome');
        $this->assertEqual(TipyInflector::classify('welcome_home'), 'WelcomeHome');
        $this->assertEqual(TipyInflector::classify('welcome_homes'), 'WelcomeHome');
        $this->assertEqual(TipyInflector::classify('welcomehomes'), 'Welcomehome');
        $this->assertEqual(TipyInflector::classify('123welcomes'), '123welcome');
        $this->assertEqual(TipyInflector::classify('projects'), 'Project');
        $this->assertEqual(TipyInflector::classify('big_projects'), 'BigProject');
        $this->assertEqual(TipyInflector::classify('mice'), 'Mouse');
    }

    public function testClassifySelf() {
        $this->assertEqual(TipyInflector::classify('Welcome'), 'Welcome');
        $this->assertEqual(TipyInflector::classify('WelcomeHome'), 'WelcomeHome');
        $this->assertEqual(TipyInflector::classify('123welcome'), '123welcome');
        $this->assertEqual(TipyInflector::classify('Project'), 'Project');
        $this->assertEqual(TipyInflector::classify('BigProjects'), 'BigProject');
        $this->assertEqual(TipyInflector::classify('Mouse'), 'Mouse');
    }

    public function testTableize() {
        $this->assertEqual(TipyInflector::tableize('Welcome'), 'welcomes');
        $this->assertEqual(TipyInflector::tableize('WelcomeHome'), 'welcome_homes');
        $this->assertEqual(TipyInflector::tableize('Welcomehome'), 'welcomehomes');
        $this->assertEqual(TipyInflector::tableize('123welcome'), '123welcomes');
        $this->assertEqual(TipyInflector::tableize('Projects'), 'projects');
        $this->assertEqual(TipyInflector::tableize('BigProject'), 'big_projects');
        $this->assertEqual(TipyInflector::tableize('Mouse'), 'mice');
    }

    public function testControllerize() {
        $this->assertEqual(TipyInflector::controllerize('welcome'), 'Welcome');
        $this->assertEqual(TipyInflector::controllerize('wELcOmE'), 'Welcome');
        $this->assertEqual(TipyInflector::controllerize('welcomes'), 'Welcomes');
        $this->assertEqual(TipyInflector::controllerize('welcome_home'), 'WelcomeHome');
        $this->assertEqual(TipyInflector::controllerize('welcome_homes'), 'WelcomeHomes');
        $this->assertEqual(TipyInflector::controllerize('welcomehomes'), 'Welcomehomes');
        $this->assertEqual(TipyInflector::controllerize('123welcomes'), '123welcomes');
        $this->assertEqual(TipyInflector::controllerize('projects'), 'Projects');
        $this->assertEqual(TipyInflector::controllerize('big_projects'), 'BigProjects');
        $this->assertEqual(TipyInflector::controllerize('mice'), 'Mice');
    }

}
