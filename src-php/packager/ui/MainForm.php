<?php
namespace packager\ui;

use php\gui\layout\UXHBox;
use php\gui\layout\UXVBox;
use php\gui\UXButton;
use php\gui\UXChoiceBox;
use php\gui\UXComboBox;
use php\gui\UXForm;
use php\gui\UXTextField;

class MainForm extends UXForm
{
    protected $__titleBar;


    public function __construct(UXForm $form)
    {
        parent::__construct($form);

        $this->layout = new UXVBox;
        $this->layout->size = [800, 600];
        $this->layout->backgroundColor = '#fff';
        $this->resizable = false;

        //$this->style = 'UNDECORATED';

        $this->layout->add($this->__titleBar = new TitleBar());

        







    }
}

final class TitleBar extends UXHBox
{
    public function __construct()
    {
        parent::__construct();

        $this->spacing = 6;
        $this->padding = 6;

        $this->backgroundColor = "#1c90ed";

        $this->add($separatorHBox = new UXHBox);
        UXHBox::setHgrow($separatorHBox, 'ALWAYS');

        $this->add($searchBox = new UXComboBox());
        $searchBox->editable = true;
        $searchBox->width = 280;

        $this->add(new UXButton('НАСТРОЙКИ-ИКОНКА'));
        $this->add(new UXButton('ВОЙТИ'));
    }
}