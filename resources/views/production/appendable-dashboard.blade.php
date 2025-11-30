

<div class="order-width">
    <div class="bg-white border-b border-neutral-200 p-6 shadow-soft">
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-[#FFF5EF] to-white border-2 border-[#231F20] rounded-xl p-5 shadow-card transition-all hover:shadow-lg align-content-center">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs text-[#231F20] uppercase font-bold tracking-wide">Total Orders</div>
                    <i class="text-[#A43026] text-lg" data-fa-i2svg=""><svg class="svg-inline--fa fa-cart-shopping" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="cart-shopping" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg="">
                            <path fill="currentColor" d="M0 24C0 10.7 10.7 0 24 0H69.5c22 0 41.5 12.8 50.6 32h411c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H170.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24 10.7 24 24s-10.7 24-24 24H199.7c-34.6 0-64.3-24.6-70.7-58.5L77.4 54.5c-.7-3.8-4-6.5-7.9-6.5H24C10.7 48 0 37.3 0 24zM128 464a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"></path>
                        </svg></i>
                </div>
                <div class="text-4xl font-bold text-[#A43026]"> {{ \App\Helpers\Helper::formatNumber($otherStatistics['total_orders']) }} </div>
            </div>
            <div class="bg-gradient-to-br from-[#FFF5EF] to-white border-2 border-[#231F20] rounded-xl p-5 shadow-card transition-all hover:shadow-lg align-content-center">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs text-[#231F20] uppercase font-bold tracking-wide">Production Required</div>
                    <i class="text-[#A43026] text-lg" data-fa-i2svg=""><svg class="svg-inline--fa fa-clipboard-list" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="clipboard-list" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" data-fa-i2svg="">
                            <path fill="currentColor" d="M192 0c-41.8 0-77.4 26.7-90.5 64H64C28.7 64 0 92.7 0 128V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V128c0-35.3-28.7-64-64-64H282.5C269.4 26.7 233.8 0 192 0zm0 64a32 32 0 1 1 0 64 32 32 0 1 1 0-64zM72 272a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zm104-16H304c8.8 0 16 7.2 16 16s-7.2 16-16 16H176c-8.8 0-16-7.2-16-16s7.2-16 16-16zM72 368a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zm88 0c0-8.8 7.2-16 16-16H304c8.8 0 16 7.2 16 16s-7.2 16-16 16H176c-8.8 0-16-7.2-16-16z"></path>
                        </svg></i>
                </div>
                <div class="text-4xl font-bold text-[#A43026]"> {{ \App\Helpers\Helper::formatNumber($otherStatistics['production_required']) }} </div>
            </div>
            <div class="bg-gradient-to-br from-[#FFF5EF] to-white border-2 border-[#231F20] rounded-xl p-5 shadow-card transition-all hover:shadow-lg align-content-center">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs text-[#231F20] uppercase font-bold tracking-wide">Produced</div>
                    <i class="text-[#F4B018] text-lg" data-fa-i2svg=""><svg class="svg-inline--fa fa-circle-check" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="circle-check" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="">
                            <path fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"></path>
                        </svg></i>
                </div>
                <div class="text-4xl font-bold text-[#F4B018]"> {{ \App\Helpers\Helper::formatNumber($otherStatistics['produced']) }} </div>
            </div>
            <div class="bg-gradient-to-br from-[#FFF5EF] to-white border-2 border-[#231F20] rounded-xl p-5 shadow-card transition-all hover:shadow-lg align-content-center">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs text-[#231F20] uppercase font-bold tracking-wide">Remaining</div>
                    <i class="text-[#A43026] text-lg" data-fa-i2svg=""><svg class="svg-inline--fa fa-hourglass-half" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="hourglass-half" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" data-fa-i2svg="">
                            <path fill="currentColor" d="M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64V75c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437v11c-17.7 0-32 14.3-32 32s14.3 32 32 32H64 320h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V437c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1V64c17.7 0 32-14.3 32-32s-14.3-32-32-32H320 64 32zM96 75V64H288V75c0 19-5.6 37.4-16 53H112c-10.3-15.6-16-34-16-53zm16 309c3.5-5.3 7.6-10.3 12.1-14.9L192 301.3l67.9 67.9c4.6 4.6 8.6 9.6 12.1 14.9H112z"></path>
                        </svg></i>
                </div>
                <div class="text-4xl font-bold text-[#A43026]"> {{ \App\Helpers\Helper::formatNumber($otherStatistics['required']) }} </div>
            </div>
        </div>
    </div>
</div>



<div class="flex h-screen">

    <div id="category-sidebar" class="w-80 bg-white border-r border-neutral-200 flex flex-col shadow-soft">
        <div class="flex-1 overflow-y-auto">
            <div class="p-3 space-y-2">

                @forelse($categoriesToShow as $categoryId => $category)
                <div class="category-item mb-2 p-4 bg-white hover:bg-[#FFF5EF] border border-neutral-200 rounded-xl cursor-pointer shadow-soft transition-all c-card" data-category_id="{{ $categoryId }}">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[#231F20] font-semibold"> {{ $category['name'] }} </span>
                    </div>
                    <div class="grid grid-cols-3 gap-3 mb-3 text-xs">
                        <div>
                            <div class="text-neutral-500 font-medium mb-0.5">Ordered</div>
                            <div class="text-[#231F20] font-bold text-sm"> {{ \App\Helpers\Helper::formatNumber($category['ordered']) }} </div>
                        </div>
                        <div>
                            <div class="text-neutral-500 font-medium mb-0.5">Produced</div>
                            <div class="text-[#F4B018] font-bold text-sm"> {{ \App\Helpers\Helper::formatNumber($category['produced']) }} </div>
                        </div>
                        <div>
                            <div class="text-neutral-500 font-medium mb-0.5">Pending</div>
                            <div class="text-[#A43026] font-bold text-sm"> {{ \App\Helpers\Helper::formatNumber($category['pending']) }} </div>
                        </div>
                    </div>
                    <div class="relative w-full h-2.5 bg-[#FFF5EF] rounded-full overflow-hidden shadow-inner">
                        <div class="absolute left-0 top-0 h-full bg-gradient-to-r from-[#F4B018] to-[#F6C341] rounded-full transition-all" style="width: 89%"></div>
                    </div>
                </div>
                @empty
                @endforelse

            </div>
        </div>
    </div>
    <div id="main-content" class="flex-1 flex flex-col overflow-hidden">

        @if($viewType == 'card')
            
            <div class="flex-1 overflow-y-auto p-6">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-xl font-bold text-[#231F20]">Cakes - Product Details</h2>
                    </div>

                    <div class="grid grid-cols-4 gap-5  grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-4">

                        @forelse ($productsToShow as $product)
                            <div class="product-card bg-gradient-to-br from-[#FDEDE7] to-[#FFF5EF] border-2 border-[#231F20] rounded-xl overflow-hidden shadow-card transition-all">
                                <div class="bg-gradient-to-r from-[#A43026] to-[#B13527] text-white px-4 py-3 flex items-center justify-between">
                                    <span class="text-sm font-semibold"> {{ $product['product_name'] }} –  {{ $product['unit_name'] }} </span>
                                </div>
                                <div class="p-5">
                                    <div class="flex items-center justify-center mb-4">
                                        <div class="relative w-32 h-32 donut-hover">
                                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                                <circle cx="50" cy="50" r="40" fill="none" stroke="#f4b43b" stroke-width="20"></circle>

                                                <circle cx="50" cy="50" r="40" fill="none" stroke="url(#gradient6)" stroke-width="20" 
                                                        stroke-dasharray="251.2" stroke-dashoffset="{{ \App\Helpers\Helper::formatNumber(251.2 - (251.2 * $product['percentage'] / 100)) }}" 
                                                        stroke-linecap="round"></circle>

                                                <circle cx="50" cy="50" r="40" fill="none" stroke="#D26A5A" stroke-width="20" 
                                                        stroke-dasharray="251.2" stroke-dashoffset="{{ \App\Helpers\Helper::formatNumber(251.2 - (251.2 * $product['percentage'] / 100)) }}" 
                                                        stroke-linecap="round"></circle>

                                                <defs>
                                                    <linearGradient id="gradient6" x1="0%" y1="0%" x2="100%" y2="100%">
                                                        <stop offset="0%" style="stop-color:#F4B018;stop-opacity:1"></stop>
                                                        <stop offset="100%" style="stop-color:#F6C341;stop-opacity:1"></stop>
                                                    </linearGradient>
                                                </defs>
                                            </svg>
                                            
                                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                                <div class="text-3xl font-bold text-[#231F20]">{{ $product['percentage'] }}%</div>
                                                <div class="text-xs text-neutral-600 font-medium">Produced</div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="space-y-2.5 text-sm">
                                        <div class="flex justify-between items-center">
                                            <span class="text-neutral-600 font-medium">Ordered:</span>
                                            <span class="text-[#231F20] font-bold">{{ \App\Helpers\Helper::formatNumber($product['ordered']) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-neutral-600 font-medium">Produced:</span>
                                            <span class="text-[#F4B018] font-bold">{{ \App\Helpers\Helper::formatNumber($product['produced']) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-neutral-600 font-medium">Pending:</span>
                                            <span class="text-[#A43026] font-bold">{{ \App\Helpers\Helper::formatNumber($product['pending']) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                        @endforelse

                    </div>

                </div>
            </div>
        @else



        <div class="relative overflow-x-auto ">
            
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 border-collapse">
                <thead class="text-xs sticky top-0 z-10" style="background-color: #5e0002;color:white;">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Category
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Product
                        </th>
                        <th scope="col" class="px-6 py-3">
                            UoM
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Ordereed
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Produced
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Pending
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productsToShow as $product)
                        <tr class="bg-white border-b" style="color:#5d0004;font-weight:600;">
                            <th scope="row" class="px-3 py-2">
                                {{ $product['category_name'] ?? 'N/A' }}
                            </th>
                            <td class="px-3 py-2">
                                {{ $product['product_name'] ?? 'N/A' }}
                            </td>
                            <td class="px-3 py-2">
                                {{ $product['unit_name'] ?? 'N/A' }}
                            </td>
                            <td class="px-3 py-2">
                                {{ \App\Helpers\Helper::formatNumber($product['ordered'] ?? 0) }}
                            </td>
                            <td class="px-3 py-2">
                                {{ \App\Helpers\Helper::formatNumber($product['produced'] ?? 0) }}
                            </td>
                            <td class="px-3 py-2">
                                {{ \App\Helpers\Helper::formatNumber($product['pending'] ?? 0) }}
                            </td>
                        </tr>                    
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>


        @endif
    </div>