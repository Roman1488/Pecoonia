<?php
/**
 * Created by PhpStorm.
 * User: mjwunderlich
 * Date: 9/28/16
 * Time: 17:44
 */

namespace App;


use App\Libraries\Utils;

trait ModelTrait
{
    /**
     * Returns an array of fields to be excluded from model when outputting to JSON.
     *
     * @return array
     */
    public function getFieldsToExclude()
    {
        if (property_exists(get_called_class(), 'fields_to_exclude'))
            return static::$fields_to_exclude;

        return [];
    }

    /**
     * Returns an array of associations (names only) to be eager loaded with the model, useful when outputting to JSON.
     *
     * @return array
     */
    public function getExportAssociations()
    {
        if (property_exists(get_called_class(), 'export_associations'))
            return static::$export_associations;

        return [];
    }

    /**
     * Excludes all fields defined by the model's $fields_to_exclude parameter.
     *
     * @return $this
     */
    public function excludeFields()
    {
        // Exclude undesired fields
        Utils::removeFields($this, $this->getFieldsToExclude());
        return $this;
    }

    /**
     * Returns a model with excluded fields (if any) and eagerly loaded associations (if any).
     * This is useful for returning models as JSON.
     *
     * @return ModelTrait
     */
    public function complete()
    {
        $object = $this;
        if ($this->getExportAssociations())
            $object = self::with($this->getExportAssociations())->where('id', $this->id)->first();
        return $object->excludeFields();
    }
}
