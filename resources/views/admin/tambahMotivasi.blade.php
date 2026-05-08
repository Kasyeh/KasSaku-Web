@extends('template.master')

@section('page_title', 'Tambah Motivasi ✨')
@section('page_subtitle', 'Ciptakan percikan inspirasi baru untuk sistem')

@section('content')
    <div class="max-w-2xl">
        {{-- Validation Errors Alert --}}
        @if ($errors->any())
            <div
                class="mb-6 bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800/30 rounded-2xl p-5 animate-fade-in">
                <div class="flex items-start gap-3">
                    <span class="material-icons-round text-rose-500 mt-0.5">error_outline</span>
                    <div>
                        <h4 class="text-sm font-bold text-rose-700 dark:text-rose-400 mb-1">Upload Gagal</h4>
                        <ul class="text-xs text-rose-600 dark:text-rose-300 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="flex items-center gap-1.5">
                                    <span class="w-1 h-1 bg-rose-400 rounded-full flex-shrink-0"></span>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="card-premium rounded-[2rem] p-8">
            <form action="{{ url('motivasi/simpan') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
                id="motivasiForm">
                @csrf

                {{-- Pilih jenis motivasi --}}
                <div>
                    <label for="tipe" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Jenis
                        Motivasi</label>
                    <select name="tipe" id="tipe"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
                        required>
                        <option value="text" {{ old('tipe') == 'text' || !old('tipe') ? 'selected' : '' }}>Teks Inspiratif
                        </option>
                        <option value="image" {{ old('tipe') == 'image' ? 'selected' : '' }}>Visual (Gambar)</option>
                    </select>
                </div>

                {{-- Input teks motivasi --}}
                <div id="input-text">
                    <label for="isi" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Pesan
                        Motivasi</label>
                    <textarea name="isi" id="isi" rows="4"
                        class="w-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none placeholder:text-slate-400 @error('isi') !border-rose-400 dark:!border-rose-500 ring-2 ring-rose-400/20 @enderror"
                        placeholder="Tuliskan kata-kata bijak di sini...">{{ old('isi') }}</textarea>
                    @error('isi')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                            <span class="material-icons-round text-sm">info</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Input gambar motivasi (Enhanced with Cropper) --}}
                <div id="input-foto" class="hidden">
                    <label for="foto" class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                        Upload Foto Motivasi <span class="text-rose-500">*</span>
                    </label>

                    {{-- File Input Area --}}
                    <div class="relative group cursor-pointer">
                        <input type="file" name="foto" id="foto" accept="image/png, image/jpeg, image/jpg, image/webp"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            onchange="handleImageSelect(this)">

                        <div
                            class="w-full bg-slate-50 dark:bg-white/5 border-2 border-dashed border-slate-200 dark:border-white/10 rounded-2xl px-6 py-10 transition-all group-hover:border-primary-500 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/10 text-center space-y-4">
                            <div
                                class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mx-auto transition-transform group-hover:scale-110">
                                <span
                                    class="material-icons-round text-3xl text-primary-600 dark:text-primary-400">add_photo_alternate</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Klik untuk upload foto</h3>
                                <p class="text-[10px] font-medium text-slate-400 mt-1 uppercase tracking-wider">Mendukung
                                    JPG, PNG, WebP (Max 6MB)</p>
                                <p class="text-[9px] font-medium text-slate-400 mt-1 italic">Rekomendasi rasio: 2:1
                                    (Landscape Banner)</p>
                            </div>
                        </div>
                    </div>
                    @error('foto')
                        <p class="text-xs font-bold text-rose-500 mt-2 flex items-center gap-1">
                            <span class="material-icons-round text-sm">error</span> {{ $message }}
                        </p>
                    @enderror

                    {{-- Image Preview & Actions --}}
                    <div id="image-preview-container" class="hidden mt-6 animate-fade-in relative group/preview">
                        <div
                            class="relative w-full aspect-[2/1] rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm">
                            <img id="preview-img" src="#" alt="Preview" class="w-full h-full object-cover">

                            {{-- Overlay Actions --}}
                            <div
                                class="absolute inset-0 bg-black/40 opacity-0 group-hover/preview:opacity-100 transition-opacity flex items-center justify-center gap-3 backdrop-blur-[2px]">
                                <button type="button" onclick="retakeCrop()"
                                    class="px-4 py-2 bg-white/20 hover:bg-white text-white hover:text-primary-600 backdrop-blur-md rounded-xl font-bold text-xs transition-all active:scale-95 flex items-center gap-2 border border-white/30">
                                    <span class="material-icons-round text-sm">crop</span> Ulangi Crop
                                </button>
                                <button type="button" onclick="removeImage()"
                                    class="px-4 py-2 bg-rose-500/80 hover:bg-rose-500 text-white backdrop-blur-md rounded-xl font-bold text-xs transition-all active:scale-95 flex items-center gap-2 border border-white/10">
                                    <span class="material-icons-round text-sm">delete</span> Hapus
                                </button>
                            </div>
                        </div>
                        <p class="text-[10px] text-center text-slate-400 mt-2 italic">
                            *Foto ini telah dicrop sesuai rasio yang optimal untuk tampilan aplikasi
                        </p>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-6 border-t border-slate-100 dark:border-white/5 flex justify-end">
                    <button type="submit" id="submitBtn"
                        class="px-8 py-3.5 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white rounded-xl shadow-lg shadow-primary-500/20 font-bold transition-all transform hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2 text-sm tracking-wide">
                        <span class="material-icons-round text-lg">save</span>
                        Simpan Motivasi
                    </button>
                    <!-- Loading State (Hidden) -->
                    <button type="button" id="loadingBtn" disabled
                        class="hidden px-8 py-3.5 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-xl font-bold cursor-wait flex items-center gap-2 text-sm tracking-wide">
                        <svg class="animate-spin h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Menyimpan...
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Crop Modal --}}
    <div id="cropModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity opacity-0" id="cropModalBackdrop">
        </div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl opacity-0 scale-95"
                    id="cropModalPanel">
                    <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-black leading-6 text-slate-900 dark:text-white" id="modal-title">
                                    Sesuaikan Foto</h3>
                                <div class="mt-4">
                                    <div class="bg-slate-900 w-full h-[400px] rounded-xl overflow-hidden relative">
                                        <img id="image-to-crop" class="max-w-full block" src="">
                                    </div>
                                    <p class="text-xs text-slate-500 mt-2 text-center">Geser dan atur ukuran kotak untuk
                                        memotong area yang diinginkan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="button" onclick="cropImage()"
                            class="inline-flex w-full justify-center rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto transition-all">
                            Terapkan & Simpan
                        </button>
                        <button type="button" onclick="cancelCrop()"
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-white dark:bg-slate-800 px-5 py-2.5 text-sm font-bold text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 sm:mt-0 sm:w-auto transition-all">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <!-- Cropper.js JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Element References
            const tipeSelect = document.getElementById('tipe');
            const inputText = document.getElementById('input-text');
            const inputFoto = document.getElementById('input-foto');
            const previewContainer = document.getElementById('image-preview-container');
            const previewImg = document.getElementById('preview-img');
            const fileInput = document.getElementById('foto');
            const form = document.getElementById('motivasiForm');
            const submitBtn = document.getElementById('submitBtn');
            const loadingBtn = document.getElementById('loadingBtn');

            // Crop Modal Elements
            const cropModal = document.getElementById('cropModal');
            const cropModalBackdrop = document.getElementById('cropModalBackdrop');
            const cropModalPanel = document.getElementById('cropModalPanel');
            const imageToCrop = document.getElementById('image-to-crop');

            let cropper = null;
            let originalFile = null;

            // --- Type Toggle Logic ---
            function toggleInput() {
                if (tipeSelect.value === 'text') {
                    inputText.classList.remove('hidden');
                    inputFoto.classList.add('hidden');
                    // If switching to text, clear image selection to prevent validation error on submission?
                    // But maybe user wants to switch back. Standard behavior: keep unless changed.
                    // However, controller ignores 'foto' if 'tipe' is text.
                } else {
                    inputText.classList.add('hidden');
                    inputFoto.classList.remove('hidden');
                }
            }

            // Bind toggle event
            tipeSelect.addEventListener('change', toggleInput);
            // Initial run
            toggleInput();


            // --- Image Selection & Validation ---
            window.handleImageSelect = function (input) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];

                    // Validate size (6MB)
                    if (file.size > 6 * 1024 * 1024) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ukuran Terlalu Besar',
                            text: 'Maksimal ukuran file adalah 6MB.',
                            confirmButtonColor: '#ef4444'
                        });
                        input.value = '';
                        return;
                    }

                    // Validate type
                    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Format Tidak Didukung',
                            text: 'Harap upload file gambar (JPG, PNG, WebP).',
                            confirmButtonColor: '#ef4444'
                        });
                        input.value = '';
                        return;
                    }

                    originalFile = file;
                    openCropModal(file);
                }
            };

            // --- Cropper Modal Logic ---
            function openCropModal(file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imageToCrop.src = e.target.result;

                    // Show modal
                    cropModal.classList.remove('hidden');
                    // Animate in
                    requestAnimationFrame(() => {
                        cropModalBackdrop.classList.remove('opacity-0');
                        cropModalPanel.classList.remove('opacity-0', 'scale-95');
                        cropModalPanel.classList.add('opacity-100', 'scale-100');
                    });

                    // Init Cropper
                    if (cropper) cropper.destroy();
                    cropper = new Cropper(imageToCrop, {
                        aspectRatio: 2 / 1, // STRICT 2:1 for Banner
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.9,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                        background: false,
                    });
                };
                reader.readAsDataURL(file);
            }

            window.cancelCrop = function () {
                // If cancelling, clear the input so user can re-select same file if they want
                // Assuming "Cancel" means "I don't want this file".
                fileInput.value = '';
                closeCropModal();
            };

            function closeCropModal() {
                // Animate out
                cropModalBackdrop.classList.add('opacity-0');
                cropModalPanel.classList.remove('opacity-100', 'scale-100');
                cropModalPanel.classList.add('opacity-0', 'scale-95');

                setTimeout(() => {
                    cropModal.classList.add('hidden');
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }
                }, 300);
            }

            // --- Crop Completion ---
            window.cropImage = function () {
                if (!cropper) return;

                // Get cropped canvas
                const canvas = cropper.getCroppedCanvas({
                    width: 1200,
                    height: 600,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                // Convert to blob
                canvas.toBlob(function (blob) {
                    // Create a new File object
                    const newFile = new File([blob], "cropped-" + originalFile.name, {
                        type: "image/jpeg",
                        lastModified: new Date().getTime()
                    });

                    // Update file input with new File
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(newFile);
                    fileInput.files = dataTransfer.files;

                    // Show Preview
                    previewImg.src = URL.createObjectURL(blob);
                    previewContainer.classList.remove('hidden');

                    // Close Modal
                    closeCropModal();

                    // Optional: Notify success
                    const Toast = Swal.mixin({
                        toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
                    });
                    Toast.fire({ icon: 'success', title: 'Foto berhasil dicrop!' });

                }, 'image/jpeg', 0.9);
            };

            window.removeImage = function () {
                fileInput.value = '';
                previewImg.src = '#';
                previewContainer.classList.add('hidden');
                originalFile = null;
            };

            window.retakeCrop = function () {
                if (originalFile) {
                    openCropModal(originalFile);
                }
            };

            // --- Form Submission ---
            form.addEventListener('submit', function (e) {
                // Basic client validation
                if (tipeSelect.value === 'image' && fileInput.files.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Foto Belum Diupload',
                        text: 'Silakan upload foto motivasi terlebih dahulu.',
                        confirmButtonColor: '#f59e0b'
                    });
                    return;
                }

                // Show loading state
                submitBtn.classList.add('hidden');
                loadingBtn.classList.remove('hidden');
            });
        });
    </script>
@endsection