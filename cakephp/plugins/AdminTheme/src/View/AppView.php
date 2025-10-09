<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace AdminTheme\View;

use Cake\View\View;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/4/en/views.html#the-app-view
 */
class AppView extends View
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like adding helpers.
     *
     * e.g. `$this->addHelper('Html');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Load shared gallery helper from main app
        $this->addHelper('Gallery');

        // Set Bootstrap-friendly form templates
        $this->Form->setTemplates([
            'inputContainer' => '<div class="{{type}}{{required}}">{{content}}</div>',
            'inputContainerError' => '<div class="{{type}}{{required}} has-validation">{{content}}{{error}}</div>',
            'error' => '<div class="invalid-feedback">{{content}}</div>',
            'label' => '<label{{attrs}} class="form-label">{{text}}</label>',
            'input' => '<input type="{{type}}" name="{{name}}" class="form-control {{attrs.class}}" {{attrs}}>',
            'textarea' => '<textarea name="{{name}}" class="form-control {{attrs.class}}" {{attrs}}>{{value}}</textarea>',
            'select' => '<select name="{{name}}" class="form-select {{attrs.class}}" {{attrs}}>{{content}}</select>',
            'checkbox' => '<input type="checkbox" name="{{name}}" class="form-check-input {{attrs.class}}" {{attrs}}>',
            'nestingLabel' => '{{hidden}}{{input}}<label{{attrs}} class="form-check-label">{{text}}</label>',
            'radioContainer' => '<div class="form-check">{{content}}</div>',
            'radio' => '<input type="radio" name="{{name}}" class="form-check-input"{{attrs}}>',
            'formGroup' => '{{label}}{{input}}',
            'inputGroupContainer' => '<div class="input-group {{type}}{{required}}">{{content}}</div>',
            'inputGroupText' => '<span class="input-group-text">{{content}}</span>',
            'inputGroupTextError' => '<span class="input-group-text">{{content}}</span>',
        ]);
    }
}
