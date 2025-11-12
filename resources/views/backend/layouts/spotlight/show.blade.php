<!-- resources/views/backend/product_listing_requests.blade.php -->
@extends('backend.app')

@section('title', 'Product Listing Requests')

@section('content')
    <!-- Listing Card -->
    <div class="container-fluid mt-5">
        <form>
            @csrf
            <div class="row gx-5 gy-8 justify-content-center">
                {{--  <div class="col-lg-4"></div>  --}}
                <!-- Left Column -->
                <div class="col-lg-9 col-auto mx-auto">
                    <div class="card">
                        <div class="card-body p-5">
                            <h2 class="fw-bold pb-3">Membership Spotlight Application</h2>
                            <div class="mb-5">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" placeholder="Product Name"
                                    value="{{ $application->name }}" readonly />
                            </div>
                            <div class="mb-5">
                                <label class="form-label">Upload Photo or Logo *</label>
                                <div class="d-flex align-items-center">
                                   <img src="{{ asset($application->image ?? 'backend/images/placeholder/image_placeholder.png') }}" alt="" class="img-fluid">
                                </div>
                            </div>
                            <div class="mb-5">
                                <label class="form-label">Business/Shop Name *</label>
                                <input type="text" name="shop_name" class="form-control" placeholder="20"
                                    value="{{ $application->shop_name }}" readonly />
                            </div>

                            <div class="mb-5">
                                <label class="form-label">Business/Shop Description *</label>
                                <textarea name="description" class="form-control" rows="3" readonly>{{ $application->shop_description }}</textarea>
                            </div>

                            <div class="mb-5">
                                <label class="form-label">Why is sustainability important to you and how do you practice
                                    it?</label>
                                <textarea name="description" class="form-control" rows="3" readonly>{{ $application->sustainability_important }}</textarea>
                            </div>

                            <div class="mb-5">
                                <label class="form-label">What impact does your business have on the community? </label>
                                <textarea name="description" class="form-control" rows="3" readonly>{{ $application->what_impact }}</textarea>
                            </div>

                            <div class="mb-5">
                                <label class="form-label">What types of community engagement are you involved in? </label>
                                <textarea name="description" class="form-control" rows="3" readonly>{{ $application->community_engagement }}</textarea>
                            </div>

                            <!-- Action Buttons Full Width Bottom -->
                            <div class="col-12 mt-8 d-flex flex-stack" id="product-actions-{{ $application->id }}">
                                <div class="d-flex gap-2 w-100">
                                    <button type="button" class="btn btn-primary w-100"
                                        id="product-actions-{{ $application->id }}"
                                        onclick="showApproveConfirm({{ $application->id }})"
                                        @if ($application->status == 'approved') disabled @endif>Apply Spotlight</button>

                                    <button type="button" class="btn btn-danger w-100"
                                        id="product-actions-{{ $application->id }}" style="background-color: #8B200C"
                                        onclick="showSaveForLaterConfirm({{ $application->id }})"
                                        @if ($application->status == 'pending') disabled @endif>Save for Later</button>

                                    <button type="button" onclick="showDeleteConfirm({{ $application->id }})"
                                        class="btn text-dark w-100" style="background-color: #E48872">Delete</button>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script')
    <script>
        function showApproveConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to approve this Spotlight application?',
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

        function showSaveForLaterConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'Spotlight application save for later?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Later Save it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    updateProductStatus(id, 'pending');
                }
            });
        }

        function updateProductStatus(id, action) {
            let url = action === 'approve' ?
                "{{ route('admin.application.approve', ':id') }}" :
                "{{ route('admin.application.pending', ':id') }}";

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
                        const container = document.getElementById(`product-actions-${id}`);
                        if (container) {
                            const approveBtn = container.querySelector('button[onclick^="showApproveConfirm"]');
                            const saveBtn = container.querySelector(
                                'button[onclick^="showSaveForLaterConfirm"]');

                            if (action === 'approve') {
                                if (approveBtn) approveBtn.disabled = true;
                                if (saveBtn) saveBtn.disabled = false;
                            } else if (action === 'pending') {
                                if (approveBtn) approveBtn.disabled = false;
                                if (saveBtn) saveBtn.disabled = true;
                            }
                        } else {
                            toastr.error(resp.message || 'Something went wrong.');
                        }
                    }
                },
                error: function(err) {
                    toastr.error('Request failed!');
                }
            });
        }

        // delete Confirm
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this record?',
                text: 'If you delete this, it will be gone forever.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteItem(id);
                }
            });
        }

        // Delete Button
        function deleteItem(id) {
            let url = "{{ route('admin.application.destroy', ':id') }}";
            let csrfToken = '{{ csrf_token() }}';
            $.ajax({
                type: "DELETE",
                url: url.replace(':id', id),
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(resp) {
                    console.log(resp);
                    if (resp.success === true) {
                        toastr.success(resp.message);
                        //console.log('deleted');
                        // Redirect after short delay to allow user to see the toast
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.members_spotlight.index') }}";
                        }, 1000);
                    } else if (resp.errors) {
                        toastr.error(resp.errors[0]);
                    } else {
                        toastr.error(resp.message);
                    }
                },
                error: function(error) {
                    toastr.error('Delete request failed!');
                }
            })
        }
    </script>
@endpush
