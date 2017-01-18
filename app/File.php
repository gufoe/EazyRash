<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class File extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    private static $safe_extensions = [
        'jpg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpeg', 'gif', 'zip', 'tar', '7z',
        'odt', 'djvu', 'ods', 'pptx', 'odp',
    ];
    private static $error;

    ///////////////////////
    //  Static functions //
    ///////////////////////

    public static function folder()
    {
        $folder = storage_path().'/uploads';
        if (!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder;
    }
    public static function getError()
    {
        return self::$error;
    }

    public static function upload($f)
    {
        if ($f == null) {
            self::$error = 'File not uploaded.';
            return;
        }

        if (!$f->isValid()) {
            self::$error = 'Error while uploading the file.';
            return false;
        }
        $ext = strtolower($f->getClientOriginalExtension());
        $name = $f->getClientOriginalName();
        $token = str_random(40);

        if (!in_array($ext, self::$safe_extensions)) {
            self::$error = 'Invalid extension, please use only one of these: .'.implode(', .', self::$safe_extensions);
            return false;
        }

        $f->move(self::folder(), $token);

        $f = new self([
            'name' => $name,
            'extension' => $ext,
            'token' => $token,
        ]);
        $f->save();

        return $f;
    }

    ////////////////////////
    //  Dynamic functions //
    ////////////////////////

    public function getSafeNameAttribute()
    {
        $name = $this->name;
        $name = str_replace('/', '|', $name);
        $name = str_replace('\\', '|', $name);
        return $name;
    }

    public function link($absolute = false)
    {
        return ($absolute ? baseurl() : '').act('file.download', $this->token);
    }

    public function view($ajaxlib = false)
    {
        if ($this->extension != 'pdf') {
            return $this->link();
        }

        $url = act('file.view', $this->token);
        if ($ajaxlib) {
            return "javascript:Ajax.show('".htmlentities(str_replace("'", "\\'", $this->name), ENT_QUOTES)."', '{$url}', '90%')";
        } else {
            return $url;
        }
    }

    public function path()
    {
        return self::folder()."/{$this->token}";
    }

    public function fullName()
    {
        return $this->name;
    }

    public function size()
    {
        if (!is_file($this->path())) {
            return 0;
        } else {
            return filesize($this->path());
        }
    }
}
