<?php

namespace Omnipay\MercadoPago;

use Omnipay\Common\Item as BaseItem;

class Item extends BaseItem
{
    public function setId($value)
    {
        return $this->setParameter('id', $value);
    }

    public function getId()
    {
        return $this->getParameter('id') ?: '';
    }

    public function setPictureUrl($value)
    {
        return $this->setParameter('picture_url', $value);
    }

    public function getPictureUrl()
    {
        return $this->getParameter('picture_url') ?: '';
    }

    public function getCategoryId()
    {
        return $this->getParameter('category_id');
    }

    public function setCategoryId($value)
    {
        return $this->setParameter('category_id', $value);
    }

    public function getCurrencyId()
    {
        return $this->getParameter('currency_id');
    }

    public function setCurrencyId($value)
    {
        return $this->setParameter('currency_id', $value);
    }
}

?>
