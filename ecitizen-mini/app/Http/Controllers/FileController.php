<?php
namespace App\Http\Controllers;

use App\Models\ApplicationFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function show(Request $r, ApplicationFile $file)
    {
        $u = $r->user();
        $app = $file->application()->with('service')->first();
        $allowed = match ($u->role) {
            'admin' => true,
            'officer' => $u->department_id && $app->service->department_id == $u->department_id,
            default => $app->user_id === $u->id,
        };
        abort_unless($allowed, 403);
        return Storage::response($file->path, $file->original_name);
    }
}
