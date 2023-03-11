Atournayre Form
================

This library helps manipulating forms.

Installation
------------

Use [Composer] to install the package:

```bash
composer require atournayre/form
```

Features
----------
* Add a maxlength attribute to all text fields 

Example
----------

```php
namespace App\Subscriber\Form;

use Atournayre\Helper\Decorator\Form\MaxLengthFormDecorator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FormDecoratorSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        // Add a maxlength attribute to all text fields.
        MaxLengthFormDecorator::decorate($form);
    }
}
```

Contribute
----------

Contributions to the package are always welcome!

* Report any bugs or issues you find on the [issue tracker].
* You can grab the source code at the package's [Git repository].

License
-------

All contents of this package are licensed under the [MIT license].

[Composer]: https://getcomposer.org

[The Community Contributors]: https://github.com/atournayre/form/graphs/contributors

[issue tracker]: https://github.com/atournayre/form/issues

[Git repository]: https://github.com/atournayre/form

[MIT license]: LICENSE
