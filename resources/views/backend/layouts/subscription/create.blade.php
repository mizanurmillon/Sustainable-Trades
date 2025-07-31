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
            <form action="{{ route('admin.subscription.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-7">
                        <div class="card card-body">
                            <h1 class="mb-4">Create Subscription</h1>
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
                                    class="form-control @error('description') is-invalid @enderror" placeholder="Enter Description">{{ old('description') }}</textarea>
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
                                <label for="type" class="form-label">Type</label>
                                <select name="type" id="type"
                                    class="form-select @error('type') is-invalid @enderror">
                                    <option value="">Select Type</option>
                                    <option value="basic" {{ old('type') == 'basic' ? 'selected' : '' }}>Basic
                                    </option>
                                    <option value="pro" {{ old('type') == 'pro' ? 'selected' : '' }}>Pro
                                    </option>
                                </select>
                                @error('interval')
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
                                    <option value="monthly" {{ old('interval') == 'monthly' ? 'selected' : '' }}>Monthly
                                    </option>
                                    <option value="yearly" {{ old('interval') == 'yearly' ? 'selected' : '' }}>Yearly
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

                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card card-body">
                            <h1 class="mb-4">Add Subscription Benefits
                                <button type="button" class="btn btn-primary btn-sm float-end"
                                    id="addMoreServiceFeature">More Benefits +</button>
                            </h1>
                            <div class="card" id="appendServiceFeature">
                                <div class="card-body border singel-service-feature">
                                    <div class="mt-4">
                                        <label for="benefit_name" class="form-label">Benefit Name</label>
                                        <input type="text" name="subscription[0][benefit_name]" id="benefit_name"
                                            class="form-control @error('benefit_name') is-invalid @enderror"
                                            placeholder="Enter Benefit Name" value="{{ old('benefit_name') }}">
                                        @error('benefit_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="mt-4">
                                        <label for="benefit_description" class="form-label">Benefit Description</label>
                                        <textarea name="subscription[0][benefit_description]" id="benefit_description"
                                            class="form-control @error('benefit_description') is-invalid @enderror" placeholder="Enter Benefit Description"
                                            value="{{ old('benefit_description') }}" rows="3"></textarea>
                                        @error('benefit_description')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="mt-4">
                                        <label for="benefit_image" class="form-label">Icon</label>
                                        <input type="file" name="subscription[0][benefit_icon]" id="benefit_image"
                                            class="form-control @error('benefit_icon') is-invalid @enderror">
                                        @error('benefit_icon')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            let benefitNumber = 0;
            $(document).on('click', '#addMoreServiceFeature', function() {
                benefitNumber++;
                let newInputGroup = `
                <div data-id="${benefitNumber}" class="card-body border singel-service-feature mt-4">
                    <button type="button" class="border-0 bg-transparent service-feature-remove btn-sm float-end" id="removeServiceFeature">
                        
                    <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="#dc3545"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    >
                    <path d="M4 7l16 0" />
                    <path d="M10 11l0 6" />
                    <path d="M14 11l0 6" />
                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                    </svg>

                    </button>
                    <div class="mt-4">
                        <label for="benefit_name" class="form-label">Benefit Name</label>
                        <input type="text" name="subscription[${benefitNumber}][benefit_name]" id="benefit_name"
                            class="form-control @error('benefit_name') is-invalid @enderror"
                            placeholder="Enter Benefit Name" value="{{ old('benefit_name') }}">
                        @error('benefit_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="benefit_description" class="form-label">Benefit Description</label>
                        <textarea name="subscription[${benefitNumber}][benefit_description]" id="benefit_description"
                            class="form-control @error('benefit_description') is-invalid @enderror" placeholder="Enter Benefit Description"
                            value="{{ old('benefit_description') }}" rows="3"></textarea>
                        @error('feature_description')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="benefit_image" class="form-label">Icon</label>
                        <input type="file" name="subscription[${benefitNumber}][benefit_icon]" id="benefit_image"
                            class="form-control @error('benefit_icon') is-invalid @enderror">
                        @error('benefit_icon')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                `;
                $('#appendServiceFeature').append(newInputGroup);
            });
             $(document).on('click','.service-feature-remove',function (){
                $(this).parent().remove();
            })
        });
    </script>
@endpush
