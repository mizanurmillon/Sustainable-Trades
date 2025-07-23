@extends('backend.app')
@section('title', 'Shop List')
@section('content')
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <div class=" container-fluid  d-flex flex-stack flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                <!--begin::Title-->
                <h1 class="text-dark fw-bold my-1 fs-2">
                    Dashboard <small class="text-muted fs-6 fw-normal ms-1"></small>
                </h1>
                <!--end::Title-->

                <!--begin::Breadcrumb-->
                <ul class="breadcrumb fw-semibold fs-base my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary"> Home </a>
                    </li>

                    <li class="breadcrumb-item text-muted"> Shop List </li>

                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Toolbar-->

    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="bg-white p-5">
                        <div class="d-flex justify-content-start mb-5">
                            <h2>Shops List</h2>
                        </div>
                        <div class="table-wrapper table-responsive mt-5">
                            <table id="data-table" class="table table-bordered mt-5">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Shop Name</th>
                                        <th>Products</th>
                                        <th>Owner Name</th>
                                        <th>City</th>
                                        <th class="text-center">Featured</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Dynamic Data --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    @push('script')
        <script type="text/javascript">
            $(function() {
                $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    }
                });

                if (!$.fn.DataTable.isDataTable('#data-table')) {
                    let dTable = $('#data-table').DataTable({
                        order: [],
                        lengthMenu: [
                            [10, 25, 50, 100, -1],
                            [10, 25, 50, 100, "All"]
                        ],
                        processing: true,
                        responsive: true,
                        serverSide: true,

                        language: {
                            processing: `<div class="text-center">
                                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                                </div>`
                        },

                        scroller: {
                            loadingIndicator: false
                        },
                        pagingType: "full_numbers",
                        dom: "<'row justify-content-between table-topbar'<'col-md-2 col-sm-4 px-0'l><'col-md-2 col-sm-4 px-0'f>>tipr",
                        ajax: {
                            url: "{{ route('admin.shops.index') }}",
                            type: "get",
                        },

                        columns: [
                            {
                                data: 'DT_RowIndex',
                                name: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'image',
                                name: 'image',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'shop_name',
                                name: 'shop_name',
                                orderable: true,
                                searchable: true
                            },
                            {
                                data: 'total_products',
                                name: 'total_products',
                                orderable: true,
                                searchable: true
                            },
                            {
                                data: 'owner_name',
                                name: 'owner_name',
                                orderable: true,
                                searchable: true
                            },
                            {
                                data: 'city',
                                name: 'city',
                                orderable: true,
                                searchable: true
                            },
                            {
                                data: 'is_featured',
                                name: 'is_featured',
                                orderable: false,
                                searchable: false
                            }
                        ],
                    });

                    dTable.buttons().container().appendTo('#file_exports');
                    new DataTable('#example', {
                        responsive: true
                    });
                }
            });

            // Status Change Confirm Alert
            function showStatusChangeAlert(id) {
                event.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'this shop will be is featured?',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        statusChange(id);
                    }
                });
            }

            // Status Change
            function statusChange(id) {
                let url = "{{ route('admin.shops.featured', ':id') }}";
                $.ajax({
                    type: "POST",
                    url: url.replace(':id', id),
                    success: function(resp) {
                        console.log(resp);
                        // Reloade DataTable
                        $('#data-table').DataTable().ajax.reload();
                        if (resp.success === true) {
                            // show toast message
                            toastr.success(resp.message);
                        } else if (resp.errors) {
                            toastr.error(resp.errors[0]);
                        } else {
                            toastr.error(resp.message);
                        }
                    },
                    error: function(error) {
                        // location.reload();
                    }
                });
            }
        </script>
    @endpush
@endsection
