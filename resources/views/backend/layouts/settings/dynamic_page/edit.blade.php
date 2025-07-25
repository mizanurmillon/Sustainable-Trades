@extends('backend.app')

@section('title', 'Update Dynamic Page')

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
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">
                            Home </a>
                    </li>

                    <li class="breadcrumb-item text-muted"> Setting </li>
                    <li class="breadcrumb-item text-muted"> Dynamic Page </li>

                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Toolbar-->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card-style mb-4">
                    <div class="card card-body">
                        <form method="POST" action="{{ route('dynamic_page.update', ['id' => $data->id]) }}">
                            @csrf
                            <div class="input-style-1 mt-4">
                                <label for="page_title">Title:</label>
                                <input type="text" placeholder="Enter Title" id="page_title"
                                    class="form-control @error('page_title') is-invalid @enderror" name="page_title"
                                    value="{{ old('page_title', $data->page_title) }}" />
                                @error('page_title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="input-style-1 mt-4">
                                <label for="sub_title">Sub Title:</label>
                                <input type="text" placeholder="Enter Sub Title" id="sub_title"
                                    class="form-control @error('sub_title') is-invalid @enderror" name="sub_title"
                                    value="{{ $data->sub_title ?? old('sub_title') }}" />
                                @error('sub_title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                             <div class="mt-4">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" name="page_image" id="image"
                                    class="dropify form-control @error('image') is-invalid @enderror" placeholder="Upload Image" data-default-file="{{ asset( $data->page_image ?? 'backend/images/placeholder/image_placeholder.png') }}">
                                @error('page_image')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="input-style-1 mt-4">
                                <label for="page_content">Content:</label>
                                <textarea placeholder="Type here..." id="page_content" name="page_content"
                                    class="form-control @error('page_content') is-invalid @enderror">
                                    {{ old('page_content', $data->page_content) }}
                                </textarea>
                                @error('page_content')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <a href="{{ route('dynamic_page.index') }}" class="btn btn-danger me-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        ClassicEditor
            .create(document.querySelector('#page_content'))
            .catch(error => {
                console.error(error);
            });
    </script>
@endpush
