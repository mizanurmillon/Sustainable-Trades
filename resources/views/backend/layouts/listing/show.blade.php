<!-- resources/views/backend/product_listing_requests.blade.php -->
@extends('backend.app')

@push('style')
    <!-- Tagify CSS -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
@endpush
@section('title', 'Product Listing Requests')

@section('content')
    <!-- Toolbar -->
    <div class="toolbar" id="kt_toolbar">
        <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
            <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                <h1 class="text-dark fw-bold my-1 fs-2">{{ $product->product_name }}</h1>
                <ul class="breadcrumb fw-semibold fs-base my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item text-muted">Add a Listing</li>
                </ul>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.listing_requests.index') }}" class="btn btn-light-primary">View Inventory</a>
            </div>
        </div>
    </div>

    <!-- Listing Card -->
    <div class="container-fluid mt-5">
        <div class="card">
            <div class="card-body p-5">
                <form>
                    @csrf
                    <div class="row gx-5 gy-8">
                        <!-- Left Column -->
                        <div class="col-lg-6">
                            <div class="mb-5">
                                <label class="form-label">Product Name / Service</label>
                                <input type="text" name="name" class="form-control" placeholder="Product Name"
                                    value="{{ $product->product_name }}" />
                            </div>
                            <div class="mb-5">
                                <label class="form-label">Product Image</label>
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    @foreach ($product->images as $image)
                                        <div class="flex-shrink-0" style="width: 150px; height: 150px;">
                                            <img src="{{ asset($image->image ?? 'backend/images/placeholder/image_placeholder.png') }}"
                                                alt="Product Image" class="img-fluid rounded"
                                                style="width: 100%; height: 100%; object-fit: cover;" />
                                        </div>
                                    @endforeach
                                    <div>
                                        <label class="btn btn-icon btn-circle btn-light-primary" for="product_image">
                                            <i class="bi bi-camera fs-2"></i>
                                        </label>
                                        {{-- <input type="file" id="product_image" name="image" class="d-none" /> --}}
                                    </div>
                                </div>
                            </div>
                            <div class="mb-5">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" class="form-control" placeholder="20"
                                    value="{{ $product->product_quantity }}" />
                            </div>

                            <div class="d-flex align-items-center mb-5 justify-content-start gap-3">
                                <label class="form-label mb-0 mt-1" style="font-size: 20px; font-weight: 600">Unlimited
                                    Stock</label>
                                <div class="mb-5 form-check form-switch mt-7">
                                    <input type="checkbox" name="unlimited_stock" class="form-check-input"
                                        @if ($product->unlimited_stock == 1) checked @endif() />
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-start gap-3">
                                <label class="form-label mb-0 mt-1" style="font-size: 20px; font-weight: 600">Out of
                                    Stock</label>
                                <div class="mb-5 form-check form-switch mt-7">
                                    <input type="checkbox" name="unlimited_stock" class="form-check-input"
                                        @if ($product->out_of_stock == 1) checked @endif() />
                                </div>

                            </div>
                            <p class="form-text p-0 m-0">Status automatically changes to “Out of Inventory” when zero
                                inventory is reached</p>

                            <div class="mb-10 mt-10">
                                <label class="form-label" style="font-size: 20px; font-weight: 600">Listing Approval
                                    Process</label>
                                <p class="form-text">
                                    To ensure all products and services on Sustainable Trades meet our sustainability
                                    standards, each listing must be approved before it goes live. Please upload a short
                                    video introducing yourself and your product or service. In the video, share details
                                    about how and where your product was made, how your food was grown, and how it
                                    aligns
                                    with our sustainability guidelines. This helps us maintain the quality and integrity
                                    of
                                    our marketplace.
                                </p>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-light me-3"><i class="bi bi-upload"></i>
                                        Upload
                                        video</button>
                                    <button type="button" class="btn btn-link">Remove video</button>
                                </div>
                            </div>
                            <div class="mb-5">
                                <label class="form-label" style="font-size: 15px; font-weight: 600">Listing
                                    Status</label>
                                <span id="status-badge-{{ $product->id }}">
                                    @if ($product->status == 'pending')
                                        <span class="badge bg-secondary">Pending</span>
                                    @elseif($product->status == 'approved')
                                        <span class="badge bg-primary text-white">Approved</span>
                                    @elseif($product->status == 'rejected')
                                        <span class="badge bg-danger text-white">Rejected</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-lg-6">
                            <div class="mb-5">
                                <label class="form-label">Price</label>
                                <input type="text" name="price" class="form-control" placeholder="$0.00"
                                    value="${{ $product->product_price }}" />
                            </div>
                            <div class="mb-5">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="5">{{ $product->description }}</textarea>
                            </div>
                            <div class="mb-5">
                                <label class="form-label">Category / Subcategory</label>
                                <select id="category-select" name="category" class="form-select">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            @if ($product->category_id == $category->id) selected @endif>
                                            {{ $category->name }}
                                        </option>
                                        @foreach ($category->subcategories as $subcategory)
                                            <option value="{{ $subcategory->id }}"
                                                @if ($product->sub_category_id == $subcategory->id) selected @endif>
                                                -- {{ $subcategory->sub_category_name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-5">
                                <label class="form-label">Fulfillment</label>
                                <select name="fulfillment" class="form-select">
                                    <option value="">Select Fulfillment</option>
                                    <option value="Shipping" @if ($product->fulfillment == 'Shipping') selected @endif>Shipping
                                    </option>
                                    <option value="Arrange Local Pickup" @if ($product->fulfillment == 'Arrange Local Pickup') selected @endif>
                                        Arrange Local Pickup</option>
                                    <option value="Arrange Local Pickup or Shipping"
                                        @if ($product->fulfillment == 'Arrange Local Pickup or Shipping') selected @endif>Arrange Local Pickup or
                                        Shipping
                                    </option>
                                </select>
                            </div>
                            <div class="mb-5">
                                <label class="form-label">Meta Tags</label>
                                <input type="text" name="meta_tags" id="meta_tags" class="form-control"
                                    placeholder="#organicsoap #naturalsoap #handmadesoap"
                                    value="{{ $product->metaTags->pluck('tag')->implode(',') }}" />
                            </div>
                            <div class="mb-5">
                                <label class="form-label">Selling Option</label>
                                <select name="selling_option" class="form-select">
                                    <option value="">Choose Below</option>
                                    <option value="Trader/Barter" @if ($product->selling_option == 'Trader/Barter') selected @endif>
                                        Trader/Barter</option>
                                    <option value="For Sale or Trader Barter"
                                        @if ($product->selling_option == 'For Sale or Trader Barter') selected @endif>For Sale or Trader Barter
                                    </option>
                                    <option value="For Sale" @if ($product->selling_option == 'For Sale') selected @endif>For
                                        Sale
                                    </option>
                                </select>
                            </div>
                            <div class="mb-5">
                                <label class="form-label">Preview Video</label>
                                <div class="ratio ratio-16x9" style="max-height: 300px; overflow: hidden;">
                                    <video controls poster="{{ asset($product->video) }}">
                                        <source src="{{ asset($product->video) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </div>
                        </div>
                        <!-- Action Buttons Full Width Bottom -->
                        <div class="col-12 mt-8 d-flex flex-stack" id="product-actions-{{ $product->id }}">
                            <div class="d-flex gap-2 w-100">
                                @if ($product->status != 'approved')
                                    <button type="button" class="btn btn-primary w-100"
                                        onclick="showApproveConfirm({{ $product->id }})">Approve</button>
                                @endif
                                @if ($product->status != 'rejected')
                                    <button type="button" class="btn btn-danger w-100" style="background-color: #8B200C"
                                        onclick="showRejectConfirm({{ $product->id }})">Deny</button>
                                @endif
                                <a href="{{ route('admin.listing_requests.index') }}" class="btn text-dark w-100"
                                    style="background-color: #E48872">Back</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <!-- Tagify CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script>
        var input = document.querySelector('#meta_tags');

        // Array of Bootstrap colors
        var colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];

        var tagify = new Tagify(input, {
            delimiters: ", ", // comma or space
            maxTags: 10,
            dropdown: {
                enabled: 0
            },
            templates: {
                tag: function(tagData) {
                    // Pick a random color for each tag
                    var color = colors[Math.floor(Math.random() * colors.length)];
                    return `<span class='tagify__tag badge bg-${color} me-1' style="margin-right:2px;" title='${tagData.value}'>
                            ${tagData.value}
                            <span class='tagify__tag__removeBtn' role='button'></span>
                        </span>`;
                }
            }
        });
    </script>
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
                    updateProductStatus(id, 'rejected');
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
                        let badgeHtml = '';
                        if (action === 'approve') {
                            badgeHtml = '<span class="badge bg-primary text-white">Approved</span>';
                        } else if (action === 'rejected') {
                            badgeHtml = '<span class="badge bg-danger text-white">Rejected</span>';
                        }
                        $(`#status-badge-${id}`).html(badgeHtml);

                        let buttonsHtml = `
                    <div class="d-flex gap-2 w-100">
                        ${action === 'approve' ? `
                                                        <button type="button" class="btn btn-danger w-100" style="background-color: #8B200C"
                                                            onclick="showRejectConfirm(${id})">Deny</button>` : `
                                                        <button type="button" class="btn btn-primary w-100"
                                                            onclick="showApproveConfirm(${id})">Approve</button>`}
                        <a href="{{ route('admin.listing_requests.index') }}" class="btn text-dark w-100"
                            style="background-color: #E48872">Back</a>
                    </div>`;
                        $(`#product-actions-${id}`).html(buttonsHtml);

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
