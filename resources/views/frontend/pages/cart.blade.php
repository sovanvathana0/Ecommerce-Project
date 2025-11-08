@extends('frontend.layouts.master')
@section('title', 'Cart Page')
@section('main-content')
	<!-- Breadcrumbs -->
	<div class="breadcrumbs">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="bread-inner">
						<ul class="bread-list">
							<li><a href="{{('home')}}">Home<i class="ti-arrow-right"></i></a></li>
							<li class="active"><a href="">Cart</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Breadcrumbs -->

	<!-- Shopping Cart -->
	<div class="shopping-cart section">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<!-- Shopping Summery -->
					<form action="{{route('cart.update')}}" method="POST">
						@csrf
						<table class="table shopping-summery">
							<thead>
								<tr class="main-hading">
									<th>PRODUCT</th>
									<th>NAME</th>
									<th class="text-center">UNIT PRICE</th>
									<th class="text-center">QUANTITY</th>
									<th class="text-center">TOTAL</th>
									<th class="text-center"><i class="ti-trash remove-icon"></i></th>
								</tr>
							</thead>
							<tbody id="cart_item_list">
								@php
									use App\Helpers\Helper;
									$cartItems = Helper::getAllProductFromCart();
								@endphp

								@if(!empty($cartItems) && is_countable($cartItems))
									@foreach($cartItems as $key => $cart)
										@php
											$photo = explode(',', $cart->product['photo']);
										@endphp
										<tr>
											<td class="image" data-title="No">
												<img src="{{ $photo[0] }}" alt="{{ $photo[0] }}">
											</td>
											<td class="product-des" data-title="Description">
												<p class="product-name">
													<a href="{{ route('product-detail', $cart->product['slug']) }}" target="_blank">
														{{ $cart->product['title'] }}
													</a>
												</p>
												<p class="product-des">{!! $cart['summary'] !!}</p>
											</td>
											<td class="price" data-title="Price">
												<span>${{ number_format($cart->price, 2) }}</span>
											</td>
											<td class="qty" data-title="Qty">
												<div class="input-group">
													<div class="button minus">
														<button type="button" class="btn btn-primary btn-number" disabled="disabled"
															data-type="minus" data-field="quant[{{ $key }}]">
															<i class="ti-minus"></i>
														</button>
													</div>
													<input type="text" name="quant[{{ $key }}]" class="input-number" data-min="1"
														data-max="100" value="{{ $cart->quantity }}">
													<input type="hidden" name="qty_id[]" value="{{ $cart->id }}">
													<div class="button plus">
														<button type="button" class="btn btn-primary btn-number" data-type="plus"
															data-field="quant[{{ $key }}]">
															<i class="ti-plus"></i>
														</button>
													</div>
												</div>
											</td>
											<td class="total-amount cart_single_price" data-title="Total"
												data-price="{{ $cart->price }}">
												<span class="money">${{ number_format($cart->amount, 2) }}</span>
											</td>
											<td class="action" data-title="Remove">
												<a href="{{ route('cart-delete', $cart->id) }}">
													<i class="ti-trash remove-icon"></i>
												</a>
											</td>
										</tr>
									@endforeach
								@else
									<tr>
										<td class="text-center" colspan="6">
											There are no any carts available.
											<a href="{{ route('product-grids') }}" style="color:blue;">Continue shopping</a>
										</td>
									</tr>
								@endif

							</tbody>
							@if(Helper::getAllProductFromCart())
								<tfoot>
									<tr>
										<td colspan="5"></td>
										<td class="text-right">
											<button class="btn" type="submit">Update</button>
										</td>
									</tr>
								</tfoot>
							@endif
						</table>
					</form>
					<!--/ End Shopping Summery -->
				</div>
			</div>
			<div class="row">
				<div class="col-12">
					<!-- Total Amount -->
					<div class="total-amount">
						<div class="row">
							<div class="col-lg-8 col-md-5 col-12">
								<div class="left">
									<div class="coupon">
										<form action="{{route('coupon-store')}}" method="POST">
											@csrf
											<input name="code" placeholder="Enter Valid Coupon">
											<button class="btn">Apply Coupon</button>
										</form>
									</div>
								</div>
							</div>
							<div class="col-lg-4 col-md-7 col-12">
								<div class="right">
									<ul>
										<li class="order_subtotal" data-price="{{Helper::totalCartPrice()}}">Cart
											Subtotal<span>${{number_format(Helper::totalCartPrice(), 2)}}</span></li>

										@if(session()->has('coupon'))
											<li class="coupon_price" data-price="{{Session::get('coupon')['value']}}">You
												Save<span>${{number_format(Session::get('coupon')['value'], 2)}}</span></li>
										@endif
										@php
											$total_amount = Helper::totalCartPrice();
											if (session()->has('coupon')) {
												$total_amount = $total_amount - Session::get('coupon')['value'];
											}
										@endphp
										@if(session()->has('coupon'))
											<li class="last" id="order_total_price">You
												Pay<span>${{number_format($total_amount, 2)}}</span></li>
										@else
											<li class="last" id="order_total_price">You
												Pay<span>${{number_format($total_amount, 2)}}</span></li>
										@endif
									</ul>
									<div class="button5">
										<a href="{{route('checkout')}}" class="btn">Checkout</a>
										<a href="{{route('product-grids')}}" class="btn">Continue shopping</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--/ End Total Amount -->
				</div>
			</div>
		</div>
	</div>
	<!--/ End Shopping Cart -->

	<!-- Start Shop Services Area  -->
	<section class="shop-services section">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-md-6 col-12">
					<!-- Start Single Service -->
					<div class="single-service">
						<i class="ti-rocket"></i>
						<h4>Free shiping</h4>
						<p>Orders over $100</p>
					</div>
					<!-- End Single Service -->
				</div>
				<div class="col-lg-3 col-md-6 col-12">
					<!-- Start Single Service -->
					<div class="single-service">
						<i class="ti-reload"></i>
						<h4>Free Return</h4>
						<p>Within 30 days returns</p>
					</div>
					<!-- End Single Service -->
				</div>
				<div class="col-lg-3 col-md-6 col-12">
					<!-- Start Single Service -->
					<div class="single-service">
						<i class="ti-lock"></i>
						<h4>Sucure Payment</h4>
						<p>100% secure payment</p>
					</div>
					<!-- End Single Service -->
				</div>
				<div class="col-lg-3 col-md-6 col-12">
					<!-- Start Single Service -->
					<div class="single-service">
						<i class="ti-tag"></i>
						<h4>Best Peice</h4>
						<p>Guaranteed price</p>
					</div>
					<!-- End Single Service -->
				</div>
			</div>
		</div>
	</section>
	<!-- End Shop Newsletter -->

	<!-- Start Shop Newsletter  -->
	@include('frontend.layouts.newsletter')
	<!-- End Shop Newsletter -->

@endsection
@push('styles')
	<style>
		li.shipping {
			display: inline-flex;
			width: 100%;
			font-size: 14px;
		}

		li.shipping .input-group-icon {
			width: 100%;
			margin-left: 10px;
		}

		.input-group-icon .icon {
			position: absolute;
			left: 20px;
			top: 0;
			line-height: 40px;
			z-index: 3;
		}

		.form-select {
			height: 30px;
			width: 100%;
		}

		.form-select .nice-select {
			border: none;
			border-radius: 0px;
			height: 40px;
			background: #f6f6f6 !important;
			padding-left: 45px;
			padding-right: 40px;
			width: 100%;
		}

		.list li {
			margin-bottom: 0 !important;
		}

		.list li:hover {
			background: #F7941D !important;
			color: white !important;
		}

		.form-select .nice-select::after {
			top: 14px;
		}
	</style>
@endpush
@push('scripts')
	<script src="{{asset('frontend/js/nice-select/js/jquery.nice-select.min.js')}}"></script>
	<script src="{{ asset('frontend/js/select2/js/select2.min.js') }}"></script>
	<script>
		$(document).ready(function () { $("select.select2").select2(); });
		$('select.nice-select').niceSelect();
	</script>
	<script>
		function updateCartTotals() {
			let cartSubtotal = 0;

			// Calculate cart subtotal from all cart items
			$('.cart_single_price').each(function () {
				let unitPrice = parseFloat($(this).data('price')) || 0;
				let quantity = parseInt($(this).closest('tr').find('.input-number').val()) || 0;
				let itemTotal = unitPrice * quantity;

				// Update the total column for this item
				$(this).find('.money').text('$' + itemTotal.toFixed(2));

				// Add to cart subtotal
				cartSubtotal += itemTotal;
			});

			// Update cart subtotal display
			$('.order_subtotal span').text('$' + cartSubtotal.toFixed(2));
			$('.order_subtotal').data('price', cartSubtotal);

			// Calculate final total with coupon discount
			let coupon = parseFloat($('.coupon_price').data('price')) || 0;
			let finalTotal = cartSubtotal - coupon;

			// Update "You Pay" amount
			$('#order_total_price span').text('$' + finalTotal.toFixed(2));
		}

		$(document).ready(function () {
			// Initialize minus button state on page load
			$('.input-number').each(function () {
				let minValue = parseInt($(this).data('min')) || 1;
				let currentVal = parseInt($(this).val()) || minValue;

				if (currentVal <= minValue) {
					$(this).closest('.input-group').find('[data-type="minus"]').attr('disabled', 'disabled');
				} else {
					$(this).closest('.input-group').find('[data-type="minus"]').removeAttr('disabled');
				}
			});

			// Handle quantity increase/decrease
			$('.btn-number').off('click').on('click', function (e) {
				e.preventDefault();

				let type = $(this).data('type');
				let input = $(this).closest('.input-group').find('.input-number');
				let minValue = parseInt(input.data('min')) || 1;
				let maxValue = parseInt(input.data('max')) || 100;
				let currentVal = parseInt(input.val()) || minValue;

				if (type === 'minus') {
					if (currentVal > minValue) {
						input.val(currentVal - 1);
					}
				} else if (type === 'plus') {
					if (currentVal < maxValue) {
						input.val(currentVal + 1);
					}
				}

				// Update button states
				let newVal = parseInt(input.val());
				if (newVal <= minValue) {
					input.closest('.input-group').find('[data-type="minus"]').attr('disabled', 'disabled');
				} else {
					input.closest('.input-group').find('[data-type="minus"]').removeAttr('disabled');
				}

				// Update totals in real-time (visual only)
				updateCartTotals();
			});

			// Handle manual input change
			$('.input-number').on('change', function () {
				let minValue = parseInt($(this).data('min')) || 1;
				let maxValue = parseInt($(this).data('max')) || 100;
				let currentVal = parseInt($(this).val()) || minValue;

				if (currentVal < minValue) {
					$(this).val(minValue);
				} else if (currentVal > maxValue) {
					$(this).val(maxValue);
				}

				// Update totals after manual change
				updateCartTotals();

				// Enable/disable minus button
				if (parseInt($(this).val()) <= minValue) {
					$(this).closest('.input-group').find('[data-type="minus"]').attr('disabled', 'disabled');
				} else {
					$(this).closest('.input-group').find('[data-type="minus"]').removeAttr('disabled');
				}
			});

			$('.shipping select[name=shipping]').change(function () {
				let cost = parseFloat($(this).find('option:selected').data('price')) || 0;
				let subtotal = parseFloat($('.order_subtotal').data('price'));
				let coupon = parseFloat($('.coupon_price').data('price')) || 0;
				$('#order_total_price span').text('$' + (subtotal + cost - coupon).toFixed(2));
			});
		});
	</script>
@endpush