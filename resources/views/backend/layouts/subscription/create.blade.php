@extends('backend.app')
@section('title', 'Create Subscription')
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

                    <li class="breadcrumb-item text-muted"> Create Subscription </li>

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
                        <h1 class="mb-4">Create Subscription</h1>
                        <form action="{{ route('admin.subscription.store') }}" method="POST">
                            @csrf

                            <div class="mt-4">
                                <label for="name" class="form-label">Plan Name</label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror" placeholder="Enter Plan name"
                                    value="{{ old('name') }}">
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" rows="4"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Enter Description">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <label for="price" class="form-label">Plan Price</label>
                                <input type="text" name="price" id="price"
                                    class="form-control @error('price') is-invalid @enderror" placeholder="Enter Plan price"
                                    value="{{ old('price') }}">
                                @error('price')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mt-4 mb-4">
                                <label for="interval" class="form-label">Plan Interval</label>
                                <select name="interval" id="interval"
                                    class="form-select @error('interval') is-invalid @enderror">
                                    <option value="">Select Interval</option>
                                    <option value="Month" {{ old('interval') == 'Month' ? 'selected' : '' }}>Monthly
                                    </option>
                                    <option value="Year" {{ old('interval') == 'Year' ? 'selected' : '' }}>Yearly
                                    </option>
                                </select>
                                @error('interval')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <input type="submit" class="btn btn-primary btn-lg" value="Submit">
                                <a href="{{ route('admin.subscription.index') }}" class="btn btn-danger btn-lg">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
