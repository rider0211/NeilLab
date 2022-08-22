{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
    <div class="container-fluid">
		<div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>Hi, welcome back!</h4>
                    <span>Popover</span>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Bootstrap</a></li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Pagination</a></li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <div class="card">
                    <div class="card-header d-block">
                        <h4 class="card-title">Pagination</h4>
                        <p class="mb-0 subtitle">Default pagination style</p>
                    </div>
                    <div class="card-body">
                        <nav>
                            <ul class="pagination">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>

                        <nav>
                            <ul class="pagination pagination-sm">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>

                        <nav>
                            <ul class="pagination pagination-xs">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6">
                <div class="card">
                    <div class="card-header d-block">
                        <h4 class="card-title">Pagination Gutter</h4>
                        <p class="mb-0 subtitle">add <code>.pagination-gutter</code> to change the style</p>
                    </div>
                    <div class="card-body">
                        <nav>
                            <ul class="pagination pagination-gutter">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                        <nav>
                            <ul class="pagination pagination-sm pagination-gutter">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                        <nav>
                            <ul class="pagination pagination-xs pagination-gutter">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
			<div class="col-xl-6 col-lg-6">
                <div class="card">
                    <div class="card-header d-block">
                        <h4 class="card-title">Pagination Color</h4>
                        <p class="mb-0 subtitle">add <code>.pagination-gutter</code> to change the style</p>
                    </div>
                    <div class="card-body">
						 <nav>
                            <ul class="pagination pagination-gutter pagination-primary no-bg">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item "><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                        <nav>
                            <ul class="pagination pagination-gutter pagination-danger">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                        <nav>
                            <ul class="pagination pagination-sm pagination-gutter pagination-info">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                        <nav>
                            <ul class="pagination pagination-xs pagination-gutter  pagination-warning">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6">
                <div class="card">
                    <div class="card-header d-block">
                        <h4 class="card-title">Pagination Circle</h4>
                        <p class="mb-0 subtitle">add <code>.pagination-circle</code> to change the style</p>
                    </div>
                    <div class="card-body">
                        <nav>
                            <ul class="pagination pagination-circle">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                        <nav>
                            <ul class="pagination pagination-sm pagination-circle">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                        <nav>
                            <ul class="pagination pagination-xs pagination-circle">
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)">4</a></li>
                                <li class="page-item page-indicator">
                                    <a class="page-link" href="javascript:void(0)">
                                        <i class="la la-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection