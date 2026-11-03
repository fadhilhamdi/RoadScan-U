<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoadScan AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('roadscan.png') }}">
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- Hero Section -->
    <header class="bg-blue-900 text-white py-9 px-6 text-center">
        <h1 class="text-4xl font-bold mb-4" style="font-family: Poppins">RoadScan AI System</h1>
        <p class="text-m max-w-2xl mx-auto opacity-90" style="font-family: Inter">
            Deep Learning (U-Net) based system for automatic road damage segmentation.
            Helps determine road damage severity based on the extent of damage through digital image analysis.
        </p>
    </header>

    <main class="container mx-auto py-10 px-4 md:px-0">
        <!-- Form Utama: Membungkus grid agar semua data terkirim -->
        <form action="/process" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Panel Kontrol (Kiri) -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Bagian 1: Konfigurasi Model & Eksperimen -->
                    <section class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center">
                            <i class="fas fa-flask mr-2 text-blue-600"></i>Detection
                        </h2>
                        
                        <div class="space-y-4">
                           

                            <!-- Toggle Mode -->
                            <div>
                                <label class="block text-xs font-semibold uppercase text-gray-500 mb-2">Detection Mode</label>
                                <div class="flex p-1 bg-gray-100 rounded-xl">
                                    <button type="button" id="btn-evaluasi" onclick="switchMode('evaluasi')" class="flex-1 py-2 text-xs font-bold rounded-lg bg-white shadow-sm text-blue-600 transition-all">
                                      Evaluation (GT)
                                    </button>
                                    <button type="button" id="btn-deteksi" onclick="switchMode('deteksi')" class="flex-1 py-2 text-xs font-bold text-gray-500 hover:text-blue-500 transition-all">
                                        Detection
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Bagian 2: Upload Data -->
                    <section class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h2 class="text-lg font-bold mb-4 text-gray-800 flex items-center">
                            <i class="fas fa-file-import mr-2 text-blue-600"></i>Upload Data
                        </h2>
                        
                        <div class="space-y-5">
                            <!-- Input Citra Kerusakan -->
                            <div class="relative group">
                                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Input Road Image</label>
                                <label for="input-image" class="mt-1 flex justify-center px-4 py-4 border-2 border-dashed border-gray-300 rounded-xl group-hover:border-blue-400 transition-colors bg-gray-50 cursor-pointer">
                                    <div class="space-y-1 text-center">
                                        <i class="fas fa-cloud-upload-alt text-gray-400 group-hover:text-blue-500 text-2xl mb-2"></i>
                                        <div class="flex text-xs text-gray-600 justify-center">
                                            <span class="relative bg-white rounded-md font-semibold text-blue-600 hover:text-blue-500">
                                                Choose File
                                            </span>
                                            <input type="file"
                                                    name="image"
                                                    id="input-image"
                                                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                                    class="sr-only"
                                                    required
                                                    onchange="validateImage(this, 'file-name-img')">
                                        </div>
                                        <p id="file-name-img" class="text-[10px] text-gray-400 mt-1 italic"></p>
                                            <p class="text-[10px] text-gray-400 mt-1">
                                                Format file: JPG / JPEG / PNG
                                            </p>
                                    </div>
                                </label>
                            </div>

                            <!-- Input Ground Truth -->
                            <div class="relative group" id="gt-input-container">
                                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Ground Truth (Mask)</label>
                                <label for="input-gt" class="mt-1 flex justify-center px-4 py-4 border-2 border-dashed border-gray-300 rounded-xl group-hover:border-green-400 transition-colors bg-gray-50 cursor-pointer">
                                    <div class="space-y-1 text-center">
                                        <i class="fas fa-check-circle text-gray-300 group-hover:text-green-500 text-2xl mb-2"></i>
                                        <div class="flex text-xs text-gray-600 justify-center">
                                            <span class="relative bg-white rounded-md font-semibold text-green-600 hover:text-green-500">
                                                Choose GT
                                            </span>
                                            <input type="file"
                                                    name="gt_image"
                                                    id="input-gt"
                                                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                                    class="sr-only"
                                                    onchange="validateImage(this, 'file-name-gt')">
                                        </div>
                                        <p id="file-name-gt" class="text-[10px] text-gray-400 mt-1 italic"></p>
                                        <p class="text-[10px] text-gray-400 mt-1">
                                            Format file: JPG / JPEG / PNG
                                        </p>
                                    </div>
                                </label>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-200 transition-all transform active:scale-95 flex items-center justify-center">
                                <i class="fas fa-play mr-2"></i> Start Detection
                            </button>
                        </div>
                    </section>

                    <!-- Bagian 3: Info Target -->
                    <div class="bg-blue-900 p-6 rounded-2xl text-white shadow-xl">
                        <h3 class="text-sm font-bold mb-2 uppercase tracking-widest opacity-70 text-blue-300">Target Evaluation</h3>
                        <ul class="text-[11px] space-y-2 opacity-90">
                            <li class="flex items-center"><i class="fas fa-check text-green-400 mr-2"></i> Pixel Segmentation (U-Net)</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-400 mr-2"></i> Severity Classification</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-400 mr-2"></i> Damage Area Calculation</li>
                        </ul>
                    </div>
                </div>

                <!-- Panel Hasil (Kanan) -->
                <div class="lg:col-span-2 space-y-6">
                    <section class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h2 class="text-xl font-semibold mb-6 border-b pb-2">
                            <i class="fas fa-eye mr-2 text-blue-600"></i>Visual Analysis Results
                        </h2>
                        
                        <!-- Visualisasi 3 Kolom -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                            <div class="space-y-2">
                                <p class="text-xs font-bold text-center text-gray-500 uppercase">Input</p>
                                <div class="border rounded-lg overflow-hidden aspect-square bg-gray-50 flex items-center justify-center">
                                    @isset($input_image)
                                        <img src="data:image/png;base64,{{ $input_image }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-image text-3xl text-gray-200"></i>
                                    @endisset
                                </div>
                            </div>
                            <div class="space-y-2">
                                <p class="text-xs font-bold text-center text-gray-500 uppercase">AI Detection Results (Mask)</p>
                                <div class="border rounded-lg overflow-hidden aspect-square bg-black flex items-center justify-center">
                                    @isset($res_mask)
                                        <img src="data:image/png;base64,{{ $res_mask }}" class="w-full h-full object-contain">
                                    @else
                                        <i class="fas fa-braille text-3xl text-white opacity-20"></i>
                                    @endisset
                                </div>
                            </div>
                            <div class="space-y-2">
                                <p class="text-xs font-bold text-center text-gray-500 uppercase">Damage Visualization</p>
                                <div class="border rounded-lg overflow-hidden aspect-square bg-gray-50 flex items-center justify-center">
                                    @isset($res_vis)
                                        <img src="data:image/png;base64,{{ $res_vis }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-layer-group text-3xl text-blue-200"></i>
                                    @endisset
                                </div>
                            </div>
                        </div>

                        <!-- Ringkasan Deteksi -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                            <div class="p-4 border border-blue-100 bg-blue-50/50 rounded-lg">
                                <h3 class="text-sm font-bold text-blue-800 uppercase mb-3 border-b border-blue-100 pb-1">Detection Results</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-medium">Crack Area</span>
                                        <span class="font-mono font-bold text-lg text-gray-800">
                                            {{ isset($res_luas) ? $res_luas . ' %' : '- %' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-medium">Damage Status</span>
                                        <span class="px-3 py-1 {{ isset($res_status) ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-600' }} rounded-full text-xs font-bold uppercase tracking-tighter">
                                            {{ $res_status ?? 'Waiting' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-4 border border-gray-100 bg-gray-50 rounded-lg flex items-center italic text-gray-500 text-sm leading-relaxed">
                                <p>Spatial analysis calculates the ratio of damaged pixels to the total road area.</p>
                            </div>
                        </div>

                        <!-- Tabel Detail Metrik -->
                        <div class="overflow-x-auto {{ isset($res_iou) ? '' : 'opacity-50' }}">
                            <p class="text-xs font-bold text-gray-400 mb-2 uppercase italic">Evaluation Metrics Table</p>
                            <table class="w-full text-sm text-left text-gray-400 border border-gray-100">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 border-b font-bold">Accuracy (%)</th>
                                        <th class="px-4 py-3 border-b font-bold">IoU</th>
                                        <th class="px-4 py-3 border-b font-bold">Dice</th>
                                        <th class="px-4 py-3 border-b font-bold">RMSE (px)</th>
                                    </tr>
                                </thead>
                               <tbody>
    <tr class="bg-white">
        @if(isset($res_luas))
            <td class="px-4 py-4 text-gray-800 font-medium">{{ $res_acc }}%</td>
            <td class="px-4 py-4 text-gray-800 font-medium">{{ $res_iou }}</td>
            <td class="px-4 py-4 text-gray-800 font-medium">{{ $res_dice }}</td>
            <td class="px-4 py-4 text-gray-800 font-medium">{{ $res_rmse }}</td>
        @else
            <td class="px-4 py-4 text-center italic text-gray-400" colspan="4">Metric data is available in Road Danage Evaluation (GT) mode.</td>
        @endif
    </tr>
</tbody>
                            </table>
                        </div>

                        <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg">
                            <p class="text-sm text-blue-800 italic text-center">
                                <strong>Note:</strong> The damage status is determined based on the percentage of crack area relative to the total image area.
                            </p>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </main>

<footer class="bg-white border-t border-gray-200 py-10 mt-100 text-center">
    <p class="text-gray-600 text-sm italic mb-4 px-4">
        "Segmentation is performed using the U-Net architecture with spatial metrics evaluation and severity level measurement."
    </p>

    <div class="text-gray-500 text-xs mb-5">
        Developed by <span class="font-semibold text-gray-700">Muhammad Fadhil Hamdi</span>
    </div>

    <div class="text-gray-400 text-xs uppercase tracking-widest">
        &copy; {{ date('Y') }} RoadScan AI System. All Rights Reserved.
    </div>
</footer>


<!-- FULLSCREEN LOADING -->
<div id="loading-overlay"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">

    <div class="bg-white px-10 py-8 rounded-2xl shadow-2xl text-center w-[320px]">

        <!-- Spinner -->
        <div class="flex justify-center mb-5">

            <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin">
            </div>

        </div>

        <!-- Text -->
        <h2 class="text-lg font-bold text-gray-800 mb-2">
           Processing Segmentation
        </h2>

        <p class="text-sm text-gray-500 mb-5">
            Please wait a moment...
        </p>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">

            <div id="loading-bar"
                 class="bg-blue-600 h-full transition-all duration-300"
                 style="width: 0%">
            </div>

        </div>

        <!-- Percentage -->
        <p id="loading-percent"
           class="mt-3 text-sm font-bold text-blue-700">
            0%
        </p>

    </div>

</div>

    <script src="{{ asset('js/script.js') }}"></script>

   <script>

    function validateImage(input, targetId) {

        const file = input.files[0];

        if (!file) return;

        const allowedTypes = [
            'image/jpeg',
            'image/png'
        ];

        if (!allowedTypes.includes(file.type)) {

            alert('Hanya file JPG dan PNG yang diperbolehkan.');

            input.value = '';

            document.getElementById(targetId).textContent = '';

            return;
        }

        document.getElementById(targetId).textContent = file.name;
    }

    // FULLSCREEN LOADING
    document.querySelector('form')
        .addEventListener('submit', function () {

            const overlay = document.getElementById('loading-overlay');
            const bar = document.getElementById('loading-bar');
            const percentText = document.getElementById('loading-percent');

            overlay.classList.remove('hidden');

            let percent = 0;

            const interval = setInterval(() => {

                percent += 5;

                if (percent > 100) {
                    percent = 100;
                }

                bar.style.width = percent + '%';

                percentText.textContent = percent + '%';

                if (percent >= 100) {
                    clearInterval(interval);
                }

            }, 120);

        });

</script>

</body>
</html>