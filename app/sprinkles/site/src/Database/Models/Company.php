<?php

namespace UserFrosting\Sprinkle\Site\Database\Models;

use UserFrosting\Sprinkle\Core\Database\Models\Model;


class Company extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'companies';

    protected $fillable = [
        'company_name',
        'email',
        'logo',
        'website',
    ];

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;

    /**
     * Delete this group from the database, along with any user associations.
     *
     * @todo What do we do with users when their group is deleted?  Reassign them?  Or, can a user be "groupless"?
     */
    public function delete()
    {
        // Delete the group
        $result = parent::delete();

        return $result;
    }

}
