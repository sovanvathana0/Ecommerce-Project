<header class="header shop">
    <!-- Topbar -->
    <div class="topbar">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-12">
                    <!-- Top Left -->
                    <div class="top-left">
                        <ul class="list-main">
                            @php
                                $settings=DB::table('settings')->get();
                            @endphp
                            <li><i class="ti-headphone-alt"></i>@foreach($settings as $data) {{$data->phone}} @endforeach</li>
                            <li><i class="ti-email"></i> @foreach($settings as $data) {{$data->email}} @endforeach</li>
                        </ul>
                    </div>
                    <!--/ End Top Left -->
                </div>
                <div class="col-lg-6 col-md-12 col-12">
                    <!-- Top Right -->
                    <div class="right-content">
                        <ul class="list-main">
                            @auth 
                                @if(Auth::user()->role=='admin')
                                <li><i class="fa fa-truck"></i> <a href="{{route('order.track')}}">Track Order</a></li>
                                    <li><i class="ti-user"></i> <a href="{{route('admin')}}"  target="_blank">Dashboard</a></li>
                                @else 
                                <li><i class="fa fa-truck"></i> <a href="{{route('order.track')}}">Track Order</a></li>
                                    <li><i class="ti-user"></i> <a href="{{route('user')}}"  target="_blank">Dashboard</a></li>
                                @endif
                                <li><i class="ti-power-off"></i> <a href="{{route('user.logout')}}">Logout</a></li>
                            @else
                                <li><i class="fa fa-sign-in"></i><a href="{{route('login.form')}}">Login /</a> <a href="{{route('register.form')}}">Register</a></li>
                            @endauth
                        </ul>
                    </div>
                    <!-- End Top Right -->
                </div>
            </div>
        </div>
    </div>
    <!-- End Topbar -->
    <div class="middle-inner">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-md-2 col-12">
                    <!-- Logo -->
                    <div class="logo">
                        @php
                            $settings=DB::table('settings')->get();
                        @endphp                    
                        <a href="{{route('home')}}"><img src="@foreach($settings as $data) {{$data->logo}} @endforeach" alt="logo"></a>
                    </div>
                    <!--/ End Logo -->
                    <!-- Search Form -->
                    <div class="search-top">
                        <div class="top-search"><a href="#0"><i class="ti-search"></i></a></div>
                        <!-- Search Form -->
                        <div class="search-top">
                            <form class="search-form">
                                <input type="text" placeholder="Search here..." name="search">
                                <button value="search" type="submit"><i class="ti-search"></i></button>
                            </form>
                        </div>
                        <!--/ End Search Form -->
                    </div>
                    <!--/ End Search Form -->
                    <div class="mobile-nav"></div>
                </div>
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="search-bar-top">
                        <div class="search-bar">
                            <select>
                                <option>All Category</option>
                                @foreach(\App\Helpers\Helper::getAllCategory() as $cat)
                                    <option>{{$cat->title}}</option>
                                @endforeach
                            </select>
                            <form method="POST" action="{{route('product.search')}}">
                                @csrf
                                <input name="search" placeholder="Search Products Here....." type="search">
                                <button class="btnn" type="submit"><i class="ti-search"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-12">
                    <div class="right-bar">
                        <!-- Wishlist -->
                        <div class="sinlge-bar shopping">
                            @php 
                                $total_prod=0;
                                $total_amount=0;
                            @endphp
                           @if(session('wishlist'))
                                @foreach(session('wishlist') as $wishlist_items)
                                    @php
                                        $total_prod+=$wishlist_items['quantity'];
                                        $total_amount+=$wishlist_items['amount'];
                                    @endphp
                                @endforeach
                           @endif
                            <a href="{{route('wishlist')}}" class="single-icon"><i class="fa fa-heart-o"></i> 
                            <span class="total-count">{{ \App\Helpers\Helper::wishlistCount() }}</span></a>
                            <!-- Shopping Item -->
                            @auth
                                <div class="shopping-item">
                                    <div class="dropdown-cart-header">
                                        <span>{{ count(\App\Helpers\Helper::getAllProductFromWishlist()) }} Items</span>
                                        <a href="{{route('wishlist')}}">View Wishlist</a>
                                    </div>
                                    <ul class="shopping-list">
                                        @forelse(\App\Helpers\Helper::getAllProductFromWishlist() as $data)
                                            @if(isset($data->product))
                                                @php
                                                    $photo = explode(',', $data->product['photo']);
                                                @endphp
                                                <li>
                                                    <a href="{{ route('wishlist-delete', $data->id) }}" class="remove" title="Remove this item">
                                                    <i class="fa fa-remove"></i>
                                                    </a>
                                                    <a class="cart-img" href="#">
                                                    <img src="{{ $photo[0] }}" alt="{{ $data->product['title'] ?? 'Product image' }}">
                                                    </a>
                                                    <h4>
                                                        <a href="{{ route('product-detail', $data->product['slug']) }}" target="_blank">
                                                        {{ $data->product['title'] }}
                                                        </a>
                                                    </h4>
                                                    <p class="quantity">
                                                        {{ $data->quantity }} x - 
                                                        <span class="amount">${{ number_format($data->price, 2) }}</span>
                                                    </p>
                                                </li>
                                            @endif
                                        @empty
                                            <li><p>No items in wishlist.</p></li>
                                        @endforelse
                                    </ul>
                                    <div class="bottom">
                                        <div class="total">
                                            <span>Total</span>
                                            <span class="total-amount">
                                            ${{ number_format(\App\Helpers\Helper::totalWishlistPrice() ?? 0, 2) }}
                                            </span>
                                        </div>
                                        <a href="{{route('cart')}}" class="btn animate">Cart</a>
                                    </div>
                                </div>
                            @endauth
                            <!--/ End Shopping Item -->
                        </div>
                        <!-- Cart -->
                        <div class="sinlge-bar shopping">
                            <a href="{{route('cart')}}" class="single-icon"><i class="fa fa-shopping-cart"></i>
                            <span class="total-count">{{ is_countable(\App\Helpers\Helper::getAllProductFromCart()) ? count(\App\Helpers\Helper::getAllProductFromCart()) : 0 }}</span></a>
                            <!-- Shopping Item -->
                            @auth
                                <div class="shopping-item">
                                    <div class="dropdown-cart-header">
                                        <span>{{ is_countable(\App\Helpers\Helper::getAllProductFromCart()) ? count(\App\Helpers\Helper::getAllProductFromCart()) : 0 }} Items</span>
                                        <a href="{{route('cart')}}">View Cart</a>
                                    </div>
                                    <ul class="shopping-list">
                                        @foreach(\App\Helpers\Helper::getAllProductFromCart() as $data)
                                            @php
                                                $photo = explode(',', $data->product['photo']);
                                            @endphp
                                            <li>
                                                <a href="{{ route('cart-delete', $data->id) }}" class="remove" title="Remove this item">
                                                    <i class="fa fa-remove"></i>
                                                </a>
                                                <a class="cart-img" href="#">
                                                    <img src="{{ $photo[0] }}" alt="{{ $photo[0] }}">
                                                </a>
                                                <h4>
                                                    <a href="{{ route('product-detail', $data->product['slug']) }}" target="_blank">
                                                        {{ $data->product['title'] }}
                                                    </a>
                                                </h4>
                                                <p class="quantity">
                                                    {{ $data->quantity }} x - 
                                                    <span class="amount">${{ number_format($data->price, 2) }}</span>
                                                </p>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="bottom">
                                        <div class="total">
                                            <span>Total</span>
                                            <span class="total-amount">${{ number_format(\App\Helpers\Helper::totalCartPrice(), 2) }}</span>
                                        </div>
                                        <a href="{{route('checkout')}}" class="btn animate">Checkout</a>
                                    </div>
                                </div>
                            @endauth
                            <!--/ End Shopping Item -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Header Inner -->
    <div class="header-inner">
        <div class="container">
            <div class="cat-nav-head">
                <div class="row">
                    <div class="col-lg-12 col-12">
                        <div class="menu-area">
                            <!-- Main Menu -->
                            <nav class="navbar navbar-expand-lg">
                                <div class="navbar-collapse">	
                                    <div class="nav-inner">	
                                        <ul class="nav main-menu menu navbar-nav">
                                            <li class="{{Request::path()=='home' ? 'active' : ''}}"><a href="{{route('home')}}">Home</a></li>
                                            <li class="{{Request::path()=='about-us' ? 'active' : ''}}"><a href="{{route('about-us')}}">About Us</a></li>
                                            <li class="@if(Request::path()=='product-grids'||Request::path()=='product-lists')  active  @endif"><a href="{{route('product-grids')}}">Products</a><span class="new">New</span></li>												
                                            <?php \App\Helpers\Helper::getHeaderCategory(); ?>							
                                            <li class="{{Request::path()=='contact' ? 'active' : ''}}"><a href="{{route('contact')}}">Contact Us</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </nav>
                            <!--/ End Main Menu -->	
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ End Header Inner -->
</header>

<style>
/* Base styles for icons */
.right-bar {
    display: flex !important;
    justify-content: flex-end;
    align-items: center;
    gap: 15px;
    visibility: visible !important;
    opacity: 1 !important;
}

.sinlge-bar.shopping {
    position: relative;
    display: inline-block !important;
}

.sinlge-bar.shopping .single-icon {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    position: relative;
    padding: 10px;
    font-size: 20px;
    color: #333;
    text-decoration: none;
    visibility: visible !important;
}

.sinlge-bar.shopping .single-icon i {
    font-size: 22px;
}

.sinlge-bar.shopping .single-icon .total-count {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #ff0000;
    color: #fff;
    border-radius: 50%;
    min-width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    padding: 0 4px;
}

/* Dropdown styles */
.sinlge-bar.shopping .shopping-item {
    position: absolute;
    right: 0;
    top: 100%;
    width: 350px;
    z-index: 99999;
    background: #fff;
    box-shadow: 0 5px 25px rgba(0,0,0,0.2);
    margin-top: 15px;
    border-radius: 5px;
    display: none;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.sinlge-bar.shopping:hover .shopping-item {
    display: block;
    opacity: 1;
    visibility: visible;
}

/* Tablet and below - Cart should be visible */
@media (max-width: 991px) {
    /* Ensure right bar is always visible */
    .col-lg-2.col-md-3.col-12 {
        display: block !important;
    }
    
    .right-bar {
        display: flex !important;
        position: relative;
        z-index: 9999;
    }
    
    .sinlge-bar.shopping {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .sinlge-bar.shopping .single-icon {
        display: inline-flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Dropdown adjustments for tablet */
    .sinlge-bar.shopping .shopping-item {
        width: 320px;
        max-width: 90vw;
        right: -10px;
    }
    
    /* Click to toggle on tablets */
    .sinlge-bar.shopping:hover .shopping-item {
        display: none;
    }
    
    .sinlge-bar.shopping.active .shopping-item {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
}

/* Mobile specific */
@media (max-width: 767px) {
    /* Force visibility of cart icons */
    .middle-inner .row {
        display: flex !important;
        flex-wrap: wrap !important;
    }
    
    .col-lg-2.col-md-3.col-12:last-child {
        display: block !important;
        width: 100%;
        margin-top: 10px;
    }
    
    .right-bar {
        display: flex !important;
        justify-content: center !important;
        gap: 25px;
    }
    
    .sinlge-bar.shopping {
        display: inline-block !important;
    }
    
    .sinlge-bar.shopping .single-icon {
        display: inline-flex !important;
        font-size: 24px;
        padding: 12px;
    }
    
    .sinlge-bar.shopping .single-icon i {
        font-size: 24px;
    }
    
    .sinlge-bar.shopping .single-icon .total-count {
        min-width: 20px;
        height: 20px;
        font-size: 12px;
    }
    
    /* Mobile dropdown */
    .sinlge-bar.shopping .shopping-item {
        width: 300px;
        right: 50%;
        transform: translateX(50%);
        margin-top: 10px;
    }
    
    .shopping-item .shopping-list {
        max-height: 250px;
        overflow-y: auto;
    }
    
    .shopping-item .shopping-list li {
        padding: 10px;
        font-size: 13px;
    }
    
    .shopping-item .shopping-list li .cart-img img {
        max-width: 50px;
    }
}

@media (max-width: 480px) {
    /* Extra small screens */
    .right-bar {
        gap: 20px;
    }
    
    .sinlge-bar.shopping .shopping-item {
        width: 280px;
    }
    
    .shopping-item .dropdown-cart-header {
        padding: 10px;
        font-size: 13px;
    }
    
    .shopping-item .bottom {
        padding: 10px;
    }
    
    .shopping-item .bottom .btn {
        padding: 8px 15px;
        font-size: 13px;
    }
}

/* Ensure proper z-index layering */
.header.shop {
    position: relative;
    z-index: 999;
}

.middle-inner {
    position: relative;
    z-index: 998;
}

/* Prevent layout issues */
.middle-inner .container,
.middle-inner .row {
    position: relative;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile and tablet dropdown toggle functionality
    if (window.innerWidth <= 991) {
        const shoppingBars = document.querySelectorAll('.sinlge-bar.shopping');
        
        shoppingBars.forEach(function(bar) {
            const icon = bar.querySelector('.single-icon');
            
            // Prevent default link behavior and toggle dropdown
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close other dropdowns
                shoppingBars.forEach(function(otherBar) {
                    if (otherBar !== bar) {
                        otherBar.classList.remove('active');
                    }
                });
                
                // Toggle current dropdown
                bar.classList.toggle('active');
                
                return false;
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.sinlge-bar.shopping')) {
                shoppingBars.forEach(function(bar) {
                    bar.classList.remove('active');
                });
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        document.querySelectorAll('.shopping-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    }
});
</script>