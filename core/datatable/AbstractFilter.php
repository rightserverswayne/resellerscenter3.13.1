<?php
namespace MGModule\ResellersCenter\Core\Datatable;

/**
 * Description of AbstractFilter
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class AbstractFilter
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param string $search
     * @return array
     */
    public function getData($search = "")
    {
        $result = $this->data;

        if($search)
        {
            $result = array_filter($this->data, function ($value) use ($search)
            {
                return (strpos($value["text"], $search) !== false);
            });
        }

        return array_values($result);
    }
}