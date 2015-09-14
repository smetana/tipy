<?php

class InflectorTest extends TipyTestCase {

    public function testSnakeCase() {
        $this->assertEqual(TipyInflector::snakeCase('Welcome'), 'welcome');
        $this->assertEqual(TipyInflector::snakeCase('WelcomeHome'), 'welcome_home');
        $this->assertEqual(TipyInflector::snakeCase('Welcomehome'), 'welcomehome');
        $this->assertEqual(TipyInflector::snakeCase('123welcome'), '123welcome');
        $this->assertEqual(TipyInflector::snakeCase('Projects'), 'projects');
        $this->assertEqual(TipyInflector::snakeCase('BigProject'), 'big_project');
        $this->assertEqual(TipyInflector::snakeCase('Mouse'), 'mouse');
        $this->assertEqual(TipyInflector::snakeCase('wELcOme'), 'w_e_lc_ome');
        $this->assertEqual(TipyInflector::snakeCase('WELCOme'), 'welc_ome');
    }

    public function testCamelCase() {
        $this->assertEqual(TipyInflector::camelCase('welcome'), 'welcome');
        $this->assertEqual(TipyInflector::camelCase('wELcOmE'), 'wELcOmE');
        $this->assertEqual(TipyInflector::camelCase('welcome_home'), 'welcomeHome');
        $this->assertEqual(TipyInflector::camelCase('welcome_homes'), 'welcomeHomes');
        $this->assertEqual(TipyInflector::camelCase('welcomehomes'), 'welcomehomes');
        $this->assertEqual(TipyInflector::camelCase('123welcomes'), '123welcomes');
        $this->assertEqual(TipyInflector::camelCase('projects'), 'projects');
        $this->assertEqual(TipyInflector::camelCase('big_projects'), 'bigProjects');
        $this->assertEqual(TipyInflector::camelCase('mice'), 'mice');
    }

    public function testtitleCase() {
        $this->assertEqual(TipyInflector::titleCase('welcome'), 'Welcome');
        $this->assertEqual(TipyInflector::titleCase('wELcOmE'), 'WELcOmE');
        $this->assertEqual(TipyInflector::titleCase('welcome_home'), 'WelcomeHome');
        $this->assertEqual(TipyInflector::titleCase('welcome_homes'), 'WelcomeHomes');
        $this->assertEqual(TipyInflector::titleCase('welcomehomes'), 'Welcomehomes');
        $this->assertEqual(TipyInflector::titleCase('123welcomes'), '123welcomes');
        $this->assertEqual(TipyInflector::titleCase('projects'), 'Projects');
        $this->assertEqual(TipyInflector::titleCase('big_projects'), 'BigProjects');
        $this->assertEqual(TipyInflector::titleCase('mice'), 'Mice');
    }

    public function testClassify() {
        $this->assertEqual(TipyInflector::classify('welcome'), 'Welcome');
        $this->assertEqual(TipyInflector::classify('wELcOme'), 'WELcOme');
        $this->assertEqual(TipyInflector::classify('welcomes'), 'Welcome');
        $this->assertEqual(TipyInflector::classify('welcome_home'), 'WelcomeHome');
        $this->assertEqual(TipyInflector::classify('welcome_homes'), 'WelcomeHome');
        $this->assertEqual(TipyInflector::classify('welcomehomes'), 'Welcomehome');
        $this->assertEqual(TipyInflector::classify('123welcomes'), '123welcome');
        $this->assertEqual(TipyInflector::classify('123_welcomes'), '123Welcome');
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
        $this->assertEqual(TipyInflector::tableize('123Welcome'), '123_welcomes');
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
