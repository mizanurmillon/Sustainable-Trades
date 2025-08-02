@extends('backend.app')
@section('title', 'Edit Subscription Benefits')
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

                    <li class="breadcrumb-item text-muted"> Edit Subscription Benefits </li>

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
                <div class="col-md-12">
                    <div class="card card-body">
                        <h1 class="mb-4">Edit Subscription Benefits</h1>
                        <form action="{{ route('admin.subscription.benefit.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card" id="appendServiceFeature">
                                <div class="card-body singel-service-feature">
                                    <div class="mt-4">
                                        <label for="benefit_name" class="form-label">Benefit Name</label>
                                        <input type="text" name="benefit_name" id="benefit_name"
                                            class="form-control @error('benefit_name') is-invalid @enderror"
                                            placeholder="Enter Benefit Name" value="{{ $data->benefit_name ?? old('benefit_name') }}">
                                        @error('benefit_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="mt-4">
                                        <label for="benefit_description" class="form-label">Benefit Description</label>
                                        <textarea name="benefit_description" id="benefit_description"
                                            class="form-control @error('benefit_description') is-invalid @enderror" placeholder="Enter Benefit Description" rows="3">{{ $data->benefit_description }}</textarea>
                                        @error('benefit_description')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="mt-4">
                                        <label for="benefit_image" class="form-label">Icon</label>
                                        <input type="file" name="benefit_icon" id="benefit_image"
                                            class="dropify form-control @error('benefit_icon') is-invalid @enderror" data-default-file="{{ asset($data->benefit_icon ?? 'backend/images/placeholder/image_placeholder.png') }}">
                                        @error('benefit_icon')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                </div>
                            </div>

                            <div class="mt-4">
                                <input type="submit" class="btn btn-primary btn-lg" value="Submit">
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-danger btn-lg">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
