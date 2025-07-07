<div class="w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 mx-auto">
   <div class="container mx-auto px-4">
      <h1 class="text-2xl font-semibold mb-4">Shopping Cart</h1>

      <div class="flex flex-col md:flex-row gap-4">
         <div class="md:w-3/4">
            <div class="bg-white overflow-x-auto rounded-lg shadow-md p-6 mb-4">
               <!-- Clear Cart Button -->
               @if (count($cart_items) > 0)
                  <div class="mb-4 flex justify-end">
                     <button
                        wire:click="clearCart"
                        wire:confirm="Are you sure you want to clear your entire cart?"
                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 disabled:opacity-50"
                        wire:loading.attr="disabled"
                        wire:target="clearCart"
                     >
                        <span
                           wire:loading.remove
                           wire:target="clearCart"
                        >Clear Cart</span>
                        <span
                           wire:loading
                           wire:target="clearCart"
                        >Clearing...</span>
                     </button>
                  </div>
               @endif

               <table class="w-full">
                  <thead>
                     <tr>
                        <th class="text-left font-semibold">Product</th>
                        <th class="text-left font-semibold">Price</th>
                        <th class="text-left font-semibold">Quantity</th>
                        <th class="text-left font-semibold">Total</th>
                        <th class="text-left font-semibold">Remove</th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse ($cart_items as $item)
                        <tr wire:key='{{ $item['product_id'] }}'>
                           <td class="py-4">
                              <div class="flex items-center">
                                 @if ($item['image'])
                                    <img
                                       class="h-16 w-16 mr-4 object-cover rounded"
                                       src="{{ url('storage', $item['image']) }}"
                                       alt="Product image"
                                    >
                                 @else
                                    <div
                                       class="h-16 w-16 mr-4 bg-gray-200 rounded flex items-center justify-center"
                                    >
                                       <span class="text-gray-500 text-xs">No
                                          Image</span>
                                    </div>
                                 @endif
                                 <span
                                    class="font-semibold">{{ $item['name'] }}</span>
                              </div>
                           </td>
                           <td class="py-4">
                              {{ Number::currency($item['unit_amount'], 'INR') }}
                           </td>
                           <td class="py-4">
                              <div class="flex items-center">
                                 <button
                                    wire:click='decreaseQty({{ $item['product_id'] }})'
                                    class="border rounded-md py-2 px-4 mr-2 hover:bg-gray-100 disabled:opacity-50"
                                    wire:loading.attr="disabled"
                                    wire:target="decreaseQty({{ $item['product_id'] }})"
                                 >
                                    <span
                                       wire:loading.remove
                                       wire:target="decreaseQty({{ $item['product_id'] }})"
                                    >-</span>
                                    <span
                                       wire:loading
                                       wire:target="decreaseQty({{ $item['product_id'] }})"
                                    >...</span>
                                 </button>
                                 <span
                                    class="text-center w-8">{{ $item['quantity'] }}</span>
                                 <button
                                    wire:click='increaseQty({{ $item['product_id'] }})'
                                    class="border rounded-md py-2 px-4 ml-2 hover:bg-gray-100 disabled:opacity-50"
                                    wire:loading.attr="disabled"
                                    wire:target="increaseQty({{ $item['product_id'] }})"
                                 >
                                    <span
                                       wire:loading.remove
                                       wire:target="increaseQty({{ $item['product_id'] }})"
                                    >+</span>
                                    <span
                                       wire:loading
                                       wire:target="increaseQty({{ $item['product_id'] }})"
                                    >...</span>
                                 </button>
                              </div>
                           </td>
                           <td class="py-4">
                              {{ Number::currency($item['total_amount'], 'INR') }}
                           </td>
                           <td>
                              <button
                                 wire:click='removeItem({{ $item['product_id'] }})'
                                 class="bg-slate-300 border-2 border-slate-400 rounded-lg px-3 py-1 hover:bg-red-500 hover:text-white hover:border-red-700 disabled:opacity-50"
                                 wire:loading.attr="disabled"
                                 wire:target="removeItem({{ $item['product_id'] }})"
                              >
                                 <span
                                    wire:loading.remove
                                    wire:target='removeItem({{ $item['product_id'] }})'
                                 >Remove</span>
                                 <span
                                    wire:loading
                                    wire:target='removeItem({{ $item['product_id'] }})'
                                 >Removing...</span>
                              </button>
                           </td>
                        </tr>
                     @empty
                        <tr>
                           <td
                              colspan="5"
                              class="text-center py-8 text-4xl font-semibold text-slate-500"
                           >
                              <div class="flex flex-col items-center">
                                 <svg
                                    class="w-16 h-16 mb-4 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                 >
                                    <path
                                       stroke-linecap="round"
                                       stroke-linejoin="round"
                                       stroke-width="2"
                                       d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 10-4 0v4.01"
                                    ></path>
                                 </svg>
                                 <p>Your cart is empty!</p>
                                 <a
                                    href="/products"
                                    class="mt-4 bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600"
                                 >
                                    Continue Shopping
                                 </a>
                              </div>
                           </td>
                        </tr>
                     @endforelse
                  </tbody>
               </table>
            </div>
         </div>

         <div class="md:w-1/4">
            <div class="bg-white rounded-lg shadow-md p-6">
               <h2 class="text-lg font-semibold mb-4">Summary</h2>
               <div class="flex justify-between mb-2">
                  <span>Subtotal</span>
                  <span>{{ Number::currency($grand_total, 'INR') }}</span>
               </div>
               <div class="flex justify-between mb-2">
                  <span>Taxes</span>
                  <span>{{ Number::currency(0, 'INR') }}</span>
               </div>
               <div class="flex justify-between mb-2">
                  <span>Shipping</span>
                  <span>{{ Number::currency(0, 'INR') }}</span>
               </div>
               <hr class="my-2">
               <div class="flex justify-between mb-2">
                  <span class="font-semibold">Grand Total</span>
                  <span
                     class="font-semibold">{{ Number::currency($grand_total, 'INR') }}</span>
               </div>
               @if (count($cart_items) > 0)
                  <a
                     href='/checkout'
                     class="bg-blue-500 block text-center text-white py-2 px-4 rounded-lg mt-4 w-full hover:bg-blue-600 transition-colors"
                  >
                     Proceed to Checkout
                  </a>
               @else
                  <button
                     disabled
                     class="bg-gray-400 block text-center text-white py-2 px-4 rounded-lg mt-4 w-full cursor-not-allowed"
                  >
                     Cart is Empty
                  </button>
               @endif
            </div>
         </div>
      </div>
   </div>
</div>
