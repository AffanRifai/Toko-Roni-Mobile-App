<?php $__env->startSection('title', 'Profil Pengguna'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50/50 py-8 px-4">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 overflow-hidden border border-gray-100">

            <div class="h-24 bg-gradient-to-r from-blue-600 to-indigo-700"></div>

            <div class="relative px-6 pb-6 text-center">
                <div class="relative -mt-12 mb-4 inline-block">
                    <?php if(auth()->user()->profile_photo_path): ?>
                        <img src="<?php echo e(asset('storage/' . auth()->user()->profile_photo_path)); ?>"
                             alt="Foto Profil"
                             class="w-24 h-24 rounded-2xl object-cover border-4 border-white shadow-md mx-auto">
                    <?php else: ?>
                        <div class="w-24 h-24 rounded-2xl bg-gradient-to-tr from-blue-500 to-indigo-400 text-white text-3xl font-bold flex items-center justify-center mx-auto border-4 border-white shadow-md">
                            <?php echo e(substr(auth()->user()->name, 0, 1)); ?>

                        </div>
                    <?php endif; ?>
                    <span class="absolute bottom-1 right-1 w-5 h-5 bg-green-500 border-4 border-white rounded-full"></span>
                </div>

                <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight"><?php echo e(auth()->user()->name); ?></h2>
                <p class="text-gray-500 font-medium text-sm"><?php echo e(auth()->user()->email); ?></p>

                <div class="mt-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                        <?php echo e(auth()->user()->role); ?>

                    </span>
                </div>
            </div>

            <div class="px-6 py-4 space-y-1">
                <div class="group p-3 rounded-xl hover:bg-gray-50 transition-colors">
                    <label class="text-[10px] uppercase font-bold text-gray-400 tracking-widest">Informasi Kontak</label>
                    <div class="flex items-center mt-1">
                        <div class="p-2 bg-gray-100 rounded-lg mr-3 group-hover:bg-white shadow-sm">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </div>
                        <p class="text-gray-700 font-semibold text-sm"><?php echo e(auth()->user()->phone ?: '-'); ?></p>
                    </div>
                </div>

                <div class="group p-3 rounded-xl hover:bg-gray-50 transition-colors">
                    <label class="text-[10px] uppercase font-bold text-gray-400 tracking-widest">Alamat Domisili</label>
                    <div class="flex items-start mt-1">
                        <div class="p-2 bg-gray-100 rounded-lg mr-3 group-hover:bg-white shadow-sm">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <p class="text-gray-700 font-semibold text-sm leading-relaxed"><?php echo e(auth()->user()->address ?: 'Alamat belum diatur'); ?></p>
                    </div>
                </div>

                <div class="group p-3 rounded-xl hover:bg-gray-50 transition-colors">
                    <label class="text-[10px] uppercase font-bold text-gray-400 tracking-widest">Waktu Bergabung</label>
                    <div class="flex items-center mt-1">
                        <div class="p-2 bg-gray-100 rounded-lg mr-3 group-hover:bg-white shadow-sm">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <p class="text-gray-700 font-semibold text-sm"><?php echo e(auth()->user()->created_at->translatedFormat('d F Y')); ?></p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gray-50/50 border-t border-gray-100">
                <a href="<?php echo e(url()->previous() ?: route('dashboard')); ?>"
                   class="flex items-center justify-center w-full py-3 bg-white border border-gray-300 rounded-xl text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-400 active:scale-[0.98] transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali Ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views\profile\edit.blade.php ENDPATH**/ ?>