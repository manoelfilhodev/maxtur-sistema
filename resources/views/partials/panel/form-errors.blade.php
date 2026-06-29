@if($errors->any())
    <div class="alert alert-danger sx-alert" role="alert" aria-live="assertive">
        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
        <div>
            <strong>Revise os campos informados</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
