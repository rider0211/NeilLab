{{-- Extends layout --}}
@extends('layout.default')



{{-- Content --}}
@section('content')
	<div class="container-fluid">
		<!-- Add Project -->
		<div class="modal fade" id="addProjectSidebar">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Create Project</h5>
						<button type="button" class="close" data-dismiss="modal"><span>&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form>
							<div class="form-group">
								<label class="text-black font-w500">Project Name</label>
								<input type="text" class="form-control">
							</div>
							<div class="form-group">
								<label class="text-black font-w500">Deadline</label>
								<input type="date" class="form-control">
							</div>
							<div class="form-group">
								<label class="text-black font-w500">Client Name</label>
								<input type="text" class="form-control">
							</div>
							<div class="form-group">
								<button type="button" class="btn btn-primary">CREATE</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
        <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>Hi, welcome back!</h4>
                    <p class="mb-0">Your business dashboard template</p>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Shop</a></li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Product Order</a></li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th class="align-middle">
                                            <div class="custom-control custom-checkbox">
												<input type="checkbox" class="form-check-input" id="checkAll">
												<label class="custom-control-label" for="checkAll"></label>
											</div>
                                        </th>
                                        <th class="align-middle">Order</th>
                                        <th class="align-middle pr-7">Date</th>
                                        <th class="align-middle" style="min-width: 12.5rem;">Ship To</th>
                                        <th class="align-middle text-right">Status</th>
                                        <th class="align-middle text-right">Amount</th>
                                        <th class="no-sort"></th>
                                    </tr>
                                </thead>
                                <tbody id="orders">
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox checkbox-success">
												<input type="checkbox" class="form-check-input" id="checkbox">
												<label class="custom-control-label" for="checkbox"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#181</strong></a> by <strong>Ricky
                                                Antony</strong><br /><a href="mailto:ricky@example.com">ricky@example.com</a></td>
                                        <td class="py-2">20/04/2020</td>
                                        <td class="py-2">Ricky Antony, 2392 Main Avenue, Penasauka, New Jersey 02149
                                            <p class="mb-0 text-500">Via Flat Rate</p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-success">Completed<span
                                                    class="ms-1 fa fa-check"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$99
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-0" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-0">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox ">
												<input type="checkbox" class="form-check-input" id="checkbox1">
												<label class="custom-control-label" for="checkbox1"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#182</strong></a> by <strong>Kin Rossow</strong><br /><a href="mailto:kin@example.com">kin@example.com</a></td>
                                        <td class="py-2">20/04/2020</td>
                                        <td class="py-2">Kin Rossow, 1 Hollywood Blvd,Beverly Hills, California 90210
                                            <p class="mb-0 text-500">Via Free Shipping
                                            </p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-primary">Processing<span
                                                    class="ms-1 fa fa-redo"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$120
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-11" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-11">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox checkbox-secondary">
												<input type="checkbox" class="form-check-input" id="checkbox2">
												<label class="custom-control-label" for="checkbox2"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#183</strong></a> by <strong>Merry
                                                Diana</strong><br /><a href="mailto:merry@example.com">merry@example.com</a></td>
                                        <td class="py-2">30/04/2020</td>
                                        <td class="py-2">Merry Diana, 1 Infinite Loop, Cupertino, California 90210
                                            <p class="mb-0 text-500">Via Link Road</p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-secondary">On
                                                Hold<span class="ms-1 fa fa-ban"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$70
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-1" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-1">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox  checkbox-warning">
												<input type="checkbox" class="form-check-input" id="checkbox3">
												<label class="custom-control-label" for="checkbox3"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#184</strong></a> by <strong>Bucky
                                                Robert</strong><br /><a href="mailto:bucky@example.com">bucky@example.com</a></td>
                                        <td class="py-2">30/04/2020</td>
                                        <td class="py-2">Bucky Robert, 1 Infinite Loop, Cupertino, California 90210
                                            <p class="mb-0 text-500">Via Free Shipping</p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-warning">Pending<span
                                                    class="ms-1 fas fa-stream"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$92
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-2" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-2">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox checkbox-secondary">
												<input type="checkbox" class="form-check-input" id="checkbox4">
												<label class="custom-control-label" for="checkbox4"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#185</strong></a> by <strong>Rocky
                                                Zampa</strong><br /><a href="mailto:rocky@example.com">rocky@example.com</a></td>
                                        <td class="py-2">30/04/2020</td>
                                        <td class="py-2">Rocky Zampa, 1 Infinite Loop, Cupertino, California 90210
                                            <p class="mb-0 text-500">Via Free Road</p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-secondary">On
                                                Hold<span class="ms-1 fa fa-ban"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$120
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-3" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-3">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox ">
												<input type="checkbox" class="form-check-input" id="checkbox5">
												<label class="custom-control-label" for="checkbox5"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#186</strong></a> by <strong>Ricky John</strong><br /><a href="mailto:ricky@example.com">ricky@example.com</a></td>
                                        <td class="py-2">30/04/2020</td>
                                        <td class="py-2">Ricky John, 1 Infinite Loop, Cupertino, California 90210
                                            <p class="mb-0 text-500">Via Free Shipping</p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-primary">Processing<span
                                                    class="ms-1 fa fa-redo"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$145
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-4" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-4">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox checkbox-success">
												<input type="checkbox" class="form-check-input" id="checkbox6">
												<label class="custom-control-label" for="checkbox6"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#187</strong></a> by <strong>Cristofer
                                                Henric</strong><br /><a href="mailto:cristofer@example.com">cristofer@example.com</a>
                                        </td>
                                        <td class="py-2">30/04/2020</td>
                                        <td class="py-2">Cristofer Henric, 1 Infinite Loop, Cupertino, California 90210
                                            <p class="mb-0 text-500">Via Flat Rate
                                            </p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-success">Completed<span
                                                    class="ms-1 fa fa-check"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$55
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-5" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-5">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox checkbox-secondary">
												<input type="checkbox" class="form-check-input" id="checkbox7">
												<label class="custom-control-label" for="checkbox7"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#188</strong></a> by <strong>Brate Lee</strong><br /><a href="mailto:lee@example.com">lee@example.com</a></td>
                                        <td class="py-2">29/04/2020</td>
                                        <td class="py-2">Brate Lee, 1 Infinite Loop, Cupertino, California 90210
                                            <p class="mb-0 text-500">Via Link Road</p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-secondary">On
                                                Hold<span class="ms-1 fa fa-ban"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$90
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-6" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-6">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox ">
												<input type="checkbox" class="form-check-input" id="checkbox8">
												<label class="custom-control-label" for="checkbox8"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#189</strong></a> by <strong>Thomas
                                                Stephenson</strong><br /><a href="mailto:Stephenson@example.com">Stephenson@example.com</a>
                                        </td>
                                        <td class="py-2">29/04/2020</td>
                                        <td class="py-2">Thomas Stephenson, 116 Ballifeary Road, Bamff
                                            <p class="mb-0 text-500">Via Flat Rate</p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-primary">Processing<span
                                                    class="ms-1 fa fa-redo"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$52
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-7" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-7">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox checkbox-success">
												<input type="checkbox" class="form-check-input" id="checkbox9">
												<label class="custom-control-label" for="checkbox9"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#190</strong></a> by <strong>Evie Singh</strong><br /><a href="mailto:eviewsing@example.com">eviewsing@example.com</a>
                                        </td>
                                        <td class="py-2">29/04/2020</td>
                                        <td class="py-2">Evie Singh, 54 Castledore Road, Tunstead
                                            <p class="mb-0 text-500">Via Flat Rate</p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-success">Completed<span
                                                    class="ms-1 fa fa-check"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$90
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-8" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-8">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox checkbox-success">
												<input type="checkbox" class="form-check-input" id="checkbox10">
												<label class="custom-control-label" for="checkbox10"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#191</strong></a> by <strong>David
                                                Peters</strong><br /><a href="mailto:peter@example.com">peter@example.com</a></td>
                                        <td class="py-2">29/04/2020</td>
                                        <td class="py-2">David Peters, Rhyd Y Groes, Rhosgoch, LL66 0AT
                                            <p class="mb-0 text-500">Via Link Road</p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-success">Completed<span
                                                    class="ms-1 fa fa-check"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$69
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-9" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-9">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="btn-reveal-trigger">
                                        <td class="py-2">
                                            <div class="custom-control custom-checkbox ">
												<input type="checkbox" class="form-check-input" id="checkbox11">
												<label class="custom-control-label" for="checkbox11"></label>
											</div>
                                        </td>
                                        <td class="py-2">
                                            <a href="#">
                                                <strong>#192</strong></a> by <strong>Jennifer
                                                Johnson</strong><br /><a href="mailto:jennifer@example.com">jennifer@example.com</a></td>
                                        <td class="py-2">28/04/2020</td>
                                        <td class="py-2">Jennifer Johnson, Rhyd Y Groes, Rhosgoch, LL66 0AT
                                            <p class="mb-0 text-500">Via Flat Rate</p>
                                        </td>
                                        <td class="py-2 text-right"><span class="badge badge-primary">Processing<span
                                                    class="ms-1 fa fa-redo"></span></span>
                                        </td>
                                        <td class="py-2 text-right">$112
                                        </td>
                                        <td class="py-2 text-right">
                                            <div class="dropdown text-sans-serif"><button class="btn btn-primary tp-btn-light sharp" type="button" id="order-dropdown-10" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="order-dropdown-10">
                                                    <div class="py-2"><a class="dropdown-item" href="javascript:void(0);">Completed</a><a class="dropdown-item" href="javascript:void(0);">Processing</a><a class="dropdown-item" href="javascript:void(0);">On Hold</a><a class="dropdown-item" href="javascript:void(0);">Pending</a>
                                                        <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="javascript:void(0);">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection