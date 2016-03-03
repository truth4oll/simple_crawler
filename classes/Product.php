<?php


/**
 * @property string $name
 * @property string $image_url
 * @property string $price
 * @property string $msrp
 * @property string $percentage
 * */
class Product implements JsonSerializable
{
    /**
     * Product name
     * @var
     */
    private $name;

    /**
     * Remote image url
     * @var
     */
    private $image_url;

    /**
     * price
     * @var
     */
    private $price;

    /**
     * Original price
     * @var
     */
    private $msrp;

    /**
     * Discount
     * @var
     */
    private $percentage;


    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = trim($value);
        }
    }


    /**
     * Returns serialized item
     * @return string
     */
    public function getSerialized()
    {
        $data = [];
        foreach (get_object_vars($this) as $property_name) {
            $data[$property_name] = $this->{$property_name};
        }
        return serialize($data);
    }

    // function called when encoded with json_encode
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }


}