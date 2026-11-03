<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SegmentationController extends Controller
{
    public function process(Request $request)
    {
        $image = $request->file('image');
        $gtImage = $request->file('gt_image');
        $expId = $request->input('experiment_id', 1);

        if (!$image) {
            return back()->with('error', 'Image wajib diupload');
        }

        try {
            $requestHttp = Http::timeout(120)
                ->attach(
                    'image',
                    fopen($image->getPathname(), 'r'),
                    $image->getClientOriginalName()
                );

            if ($gtImage) {
                $requestHttp = $requestHttp->attach(
                    'gt_image',
                    fopen($gtImage->getPathname(), 'r'),
                    $gtImage->getClientOriginalName()
                );
            }

            $response = $requestHttp->post('http://127.0.0.1:5000/predict', [
                'experiment_id' => $expId,
            ]);

            if (!$response->successful()) {
                return back()->with('error', 'Flask Error: ' . $response->body());
            }

            $result = $response->json();

            return view('segmentasi', [
                'res_mask'   => $result['mask_base64'] ?? null,
                'res_vis'    => $result['vis_base64'] ?? null,
                'res_luas'   => $result['luas_retakan'] ?? null,
                'res_status' => $result['status'] ?? null,
                'res_acc'    => $result['akurasi'] ?? null,
                'res_iou'    => $result['iou'] ?? null,
                'res_dice'   => $result['dice'] ?? null,
                'res_rmse'   => $result['rmse'] ?? null,
                'input_image' => base64_encode(file_get_contents($image)),
                'exp_id'     => $expId
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}