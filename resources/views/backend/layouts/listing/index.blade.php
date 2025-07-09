@extends('backend.app')
@section('title', 'Product listing requests')
@section('content')
    <div class="container-fluid py-4">

        <!-- Header -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h2 class="fw-bold m-0">Product listing requests</h2>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-secondary">Pending <span class="badge bg-light text-dark">{{ $products->count() }}</span></button>
                <button class="btn btn-secondary">All <span class="badge bg-light text-dark">{{ $product }}</span></button>
            </div>
        </div>

        <!-- Search bar -->
        <div class="input-group mb-4">
            <input type="text" class="form-control" placeholder="Search listings...">
            <button class="btn btn-secondary" type="button">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <!-- Cards -->
        <div class="row">
            <div class="col-md-12 col-lg-6 col-xl-12 mb-4">
                <div class="card shadow-sm h-100">
                    @foreach ($products as $product)
                    <div class="row g-0 border border-light mb-1 rounded-5 overflow-hidden">
                        <!-- Image section -->
                        <div class="col-md-2 d-none d-md-block">
                            <div class="position-relative h-100">
                                <a href="{{ asset( $product->images->first()->image ?? 'backend/images/placeholder/image_placeholder.png') }}" data-lightbox="roadtrip" target="_blank"><img src="{{ asset( $product->images->first()->image ?? 'backend/images/placeholder/image_placeholder.png') }}"
                                    class="img-fluid rounded-start w-100 h-100 object-fit-cover" alt="...">,</a>
                                <div class="position-absolute bottom-0 end-0 m-2">
                                    <span class="badge bg-white text-dark"><i class="fa-solid fa-camera me-1"></i> {{ $product->images->count() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Content section -->
                        <div class="col-md-10 col-12">
                            <div class="card-body d-flex flex-column justify-content-between h-100">

                                <!-- Top Row: Date Right Side -->
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title mb-1">{{ $product->product_name }}</h5>
                                        <p class="card-text"><small class="text-muted">Owner: {{ $product->shop->user->first_name }} {{ $product->shop->user->last_name }}</small></p>
                                    </div>
                                    <div class="text-md-end">
                                        <p class="text-muted mb-0">Request Date: {{ $product->created_at->format('d F Y') }}</p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <p class="card-text mt-3">
                                    {{ $product->description }}
                                </p>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                                    <a href="#" class="btn btn-sm" style="background-color:#D4E2CB; color: #274F45 ">More Details <i class="fa-solid fa-arrow-right"></i></a>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-danger btn-sm" style="background-color:#8B200C; ">Deny <i
                                                class="fas fa-times ms-1"></i></button>
                                        <button class="btn btn-primary btn-sm" type="button">Approve</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>
               
            </div>
        </div>
    </div>
@endsection
