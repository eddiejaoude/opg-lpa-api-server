<?php

namespace Infrastructure\Layout;

use Zend\View\Model\ViewModel;

interface LayoutControllerInterface
{
    public function prepareViewModel(
        ViewModel $viewModel
    );
}
