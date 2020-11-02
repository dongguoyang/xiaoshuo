<?php
namespace App\Admin\Forms;

use Encore\Admin\Widgets\Form as BaseForm;
class Form extends BaseForm
{
    /**
     * Get variables for render form.
     *
     * @return array
     */
    protected function getVariables()
    {
        $data = $this->data();
        foreach ($this->fields as $field) {
            $field->fill($data);
        }
        collect($this->fields())->each->fill($this->data());

        return [
            'fields'     => $this->fields,
            'attributes' => $this->formatAttribute(),
            'method'     => $this->attributes['method'],
            'buttons'    => $this->buttons,
            'width'      => $this->width,
        ];
    }
}