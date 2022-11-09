<?php

namespace Rickytech\Library\Filter;

class SQLFilter
{
    protected array $query = [];

    public function __construct(public readonly ?array $data)
    {
    }

    public function apply(): string
    {
        foreach ($this->getData() as $name => $value) {
            echo $name;
            if (method_exists($this, $name)) {
                echo $name;
                $this->query[] = call_user_func_array([$this, $name], array_filter([$value]));
            }
        }

        return implode(' AND ', $this->query);
    }

    private function getData()
    {
        return $this->data;
    }

    protected function getInstrQuery(string $field, string $keyword): string
    {
        return "INSTR(`{$field}`, {$keyword}) > 0";
    }

    protected function getWhereQuery(string $field, string $keyword): string
    {
        return "WHERE `{$field}` = `{$keyword}`";
    }
}
