<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Screenshot;
use Illuminate\Support\Facades\Auth;

class ScreenshotController extends Controller {
    public function store(Request $request) {
        if (!$request->has('image')) {
            return response()->json(['error' => 'Nebolo poslané žiadne dáta'], 400);
        }

        $user = Auth::user();
        $imageData = $request->input('image');

        $image = str_replace('data:image/png;base64,', '', $imageData);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

        $fileName = 'screenshot_' . time() . '.png';
        $filePath = 'screenshots/' . $fileName;

        Storage::disk('public')->put($filePath, $imageData);

        $screenshot = Screenshot::create([
            'user_id' => $user->id,
            'image_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Screenshot uložený!',
            'screenshot' => asset('storage/' . $filePath)
        ], 201);
    }

    public function index() {
        $screenshots = Screenshot::where('user_id', Auth::id())->latest()->get();
        return view('screenshots.index', compact('screenshots'));
    }

    public function destroy($id) {
        $screenshot = Screenshot::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$screenshot) {
            return response()->json(['error' => 'Screenshot neexistuje alebo nemáte povolenie ho zmazať.'], 403);
        }

        Storage::disk('public')->delete($screenshot->image_path);

        $screenshot->delete();

        return response()->json(['message' => 'Screenshot bol úspešne vymazaný!']);
    }

}
