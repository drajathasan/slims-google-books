<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-08-29 21:16:16
 * @modify date 2022-08-29 21:19:47
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Models;

use Zein\Database\Dages\SLiMSModelContract;

class Biblio extends SLiMSModelContract
{
    protected $PrimaryKey = 'biblio_id';
    protected $Created_at = 'input_date';
    protected $Updated_at = 'last_update';
}