@extends('backend.app')
@section('title', 'Product listing requests')
@section('content')
    <div class="container-fluid py-4">

        <!-- Header -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h2 class="fw-bold m-0">Product listing requests</h2>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.listing_requests.index', ['status' => 'pending']) }}" class="btn btn-secondary">
                    Pending <span
                        class="badge bg-light text-dark">{{ \App\Models\Product::where('status', 'pending')->count() }}</span>
                </a>

                <a href="{{ route('admin.listing_requests.index') }}" class="btn btn-secondary">
                    All <span class="badge bg-light text-dark">{{ $product }}</span>
                </a>
            </div>
        </div>

        <!-- Search bar -->
        <form method="GET" action="{{ route('admin.listing_requests.index') }}">
            <div class="input-group mb-4">

                <input type="text" name="name" class="form-control" placeholder="Search by name...">
                <button class="btn btn-secondary" type="submit" value="{{ old('name') }}">
                    <i class="fas fa-search"></i>
                </button>

            </div>
        </form>

        <!-- Cards -->
        <div class="row">
            <div class="col-md-12 col-lg-6 col-xl-12 mb-4">
                <div class="card shadow-sm h-100">
                    @foreach ($products as $product)
                        <div class="row g-0 border border-light mb-1 rounded-5 overflow-hidden">
                            <!-- Image section -->
                            <div class="col-md-2 d-none d-md-block">
                                <div class="position-relative h-100">
                                    <a href="{{ asset($product->images->first()->image ?? 'backend/images/placeholder/image_placeholder.png') }}"
                                        data-lightbox="roadtrip" target="_blank"><img
                                            src="{{ asset($product->images->first()->image ?? 'backend/images/placeholder/image_placeholder.png') }}"
                                            class="img-fluid rounded-start w-100 h-100 object-fit-cover"
                                            alt="..." style="width: 100px;height: 70px;">,</a>
                                    <div class="position-absolute bottom-0 end-0 m-2">
                                        <span class="badge bg-white text-dark"><i class="fa-solid fa-camera me-1"></i>
                                            {{ $product->images->count() }}</span>
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
                                            <p class="card-text"><small class="text-muted">Owner:
                                                    {{ $product->shop->user->first_name }}
                                                    {{ $product->shop->user->last_name }}</small></p>
                                        </div>
                                        <div class="text-md-end">
                                            <p class="text-muted mb-0">Request Date:
                                                {{ $product->created_at->format('d F Y') }}</p>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <p class="card-text mt-3">
                                        {{ $product->description }}
                                    </p>

                                    <!-- Action Buttons -->
                                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2"
                                        id="product-actions-{{ $product->id }}">
                                        <a href="{{ route('admin.product.show', $product->id) }}" class="btn btn-sm"
                                            style="background-color:#D4E2CB; color: #274F45 ">More Details <i
                                                class="fa-solid fa-arrow-right"></i></a>
                                        <div class="d-flex gap-2">
                                            @if ($product->status == 'rejected')
                                                <span class="badge text-danger">Rejected</span>
                                                <button class="btn btn-primary btn-sm" type="button"
                                                    onclick="showApproveConfirm({{ $product->id }})">Approve</button>
                                            @else
                                                <button class="btn btn-danger btn-sm" style="background-color:#8B200C;"
                                                    onclick="showRejectConfirm({{ $product->id }})">Deny <i
                                                        class="fas fa-times ms-1"></i></button>
                                            @endif

                                            @if ($product->status == 'pending')
                                                <button class="btn btn-primary btn-sm" type="button"
                                                    onclick="showApproveConfirm({{ $product->id }})">Approve</button>
                                            @elseif($product->status == 'approved')
                                                <span class="badge text-primary">Approved</span>
                                            @endif
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

@push('script')
    <script>
        function showApproveConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to approve this product?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Approve it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    updateProductStatus(id, 'approve');
                }
            });
        }

        function showRejectConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to reject this product?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Reject it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    updateProductStatus(id, 'reject');
                }
            });
        }

        function updateProductStatus(id, action) {
            let url = action === 'approve' ?
                "{{ route('admin.product.approve', ':id') }}" :
                "{{ route('admin.product.reject', ':id') }}";

            url = url.replace(':id', id);

            $.ajax({
                type: "POST",
                url: url,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(resp) {
                    if (resp.success === true) {
                        toastr.success(resp.message);

                        // Replace UI dynamically
                        let html = '';
                        if (action === 'approve') {
                            html = `
                            <button class="btn btn-danger btn-sm" style="background-color:#8B200C;"
                                                    onclick="showRejectConfirm(${id})">Deny <i
                                                        class="fas fa-times ms-1"></i></button>
                            <span class="badge text-primary">Approved</span>`;
                        } else {
                            html =
                                `
                            <span class="badge text-danger">Rejected</span>
                            <button class="btn btn-primary btn-sm" type="button" onclick="showApproveConfirm(${id})">Approve</button>`;
                        }
                        $(`#product-actions-${id} .d-flex.gap-2`).html(html);
                    } else {
                        toastr.error(resp.message || 'Something went wrong.');
                    }
                },
                error: function(err) {
                    toastr.error('Request failed!');
                }
            });
        }
    </script>
@endpush
