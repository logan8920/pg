@extends('layouts.main')
@section('title')
    - Role | Assign Permission
@endsection
@section('css')
    <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <x-pageheading :heading="'Assign Permission'" :navigation="['User Managment', 'Role', 'Assign Permission']" :description="$description ?? null" />
        <form action="{{ route('role.assginPermssion.post', $role->id) }}" method="post" validate id="regForm">
            @csrf
            <!-- DataTales Example -->
            <div class="card shadow mb-4">
                <div class="card-header d-flex float-right justify-content-between py-3 w-100">
                    <h6 class="align-content-around font-weight-bold justify-content-lg-between m-0 text-primary">Assign
                        Permission To Role | {{ ucwords($role->name) }}
                    </h6>
                </div>
                <div class="card-body bg-light">
                    <div class="row mx-2 border">
                        @php $counter = 0; @endphp
                        @if (count($permissions->toArray()))
                            @foreach ($permissions as $permission)
                                <div class="col-sm-12 border p-3 bg-gray-200">
                                    <div class="form-check">
                                        <input required type="checkbox" {!! in_array($permission->id, $rolePermission) ? 'checked' : '' !!} name="permission[]"
                                            value="{{ $permission->id }}" class="form-check-input"
                                            id="exampleCheck{{ ++$counter }}" parent="{{ $permission->id }}">
                                        <label class="form-check-label" for="exampleCheck{{ $counter }}"
                                            parent="{{ $permission->id }}">{{ ucwords(str_replace('-', ' ', $permission->name)) }}</label>
                                    </div>
                                </div>
                                @if (count($permission->children))
                                    @foreach ($permission->children as $child)
                                        <div class="col-sm-3 border p-3">
                                            <div class="form-check">
                                                <input required sub="{{ $permission->id }}" type="checkbox"
                                                    {!! in_array($child['id'], $rolePermission) ? 'checked' : '' !!} name="permission[]" value="{{ $child['id'] }}"
                                                    class="form-check-input" id="exampleCheck{{ ++$counter }}">
                                                <label sub="{{ $permission->id }}" class="form-check-label"
                                                    for="exampleCheck{{ $counter }}">{{ str_replace('-', ' ', ucwords($child['name'])) }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-sm-12 border p-3">
                                        <div class="">
                                            <label class="form-check-label">No Sub Permission Found in
                                                {{ ucwords(str_replace('-', ' ', $permission->name)) }}</label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="col-sm-12 border p-3">
                                <div class="">
                                    <label class="form-check-label">No Permission Found.</label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-danger" type="submit"><i class="fas fa-paper-plane"></i> &nbsp;Submit</button>
                    <button class="btn btn-warning" type="reset"><i class="fas fa-redo"></i> &nbsp;Cancel</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/additional-methods.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Form Validation
            $('form[validate]').validate({
                errorPlacement: function(error, element) {
                    if (element.closest('.input-group')?.length) {
                        error.insertAfter(element.closest('.input-group'));
                    } else {
                        if ($(element).hasClass("after-parent")) {
                            error.addClass("text-center w-100").insertAfter(element[0].parentElement)
                        } else {
                            error.insertAfter(element);
                        }
                    }
                }
            });


            //checkbox click handle
            $(document).on("click", "[parent]", function() {

                const parentId = this.attributes.parent.textContent;
                const allSub = $(this).closest('.card-body').find(`input[sub="${parentId}"]`).get();
                const flag = this.checked
                allSub.forEach(input => {
                    input.checked = flag
                })
                //allSub.forEach(input => $(input).closest('.sub-mod').trigger("click"))
                //this.checked = flag

            });

            $(document).on("click", "[sub]", function(event) {
                const parentId = this.attributes.sub.textContent;
                const parent = this.closest('.card-body').querySelector(`input[parent="${parentId}"]`);
                if(parent.checked) return;
                this.checked && (parent.checked = true);
            });

        });
    </script>
@endsection
