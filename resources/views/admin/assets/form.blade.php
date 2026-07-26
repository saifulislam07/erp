@php $asset = $asset ?? null; @endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $asset->name ?? '') }}">
            @error('name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="serial_number">Serial Number</label>
            <input type="text" name="serial_number" id="serial_number" class="form-control" value="{{ old('serial_number', $asset->serial_number ?? '') }}">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" name="category" id="category" class="form-control" value="{{ old('category', $asset->category ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', $asset->quantity ?? 1) }}">
            @error('quantity')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="purchase_price">Purchase Price</label>
            <input type="number" step="0.01" name="purchase_price" id="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" value="{{ old('purchase_price', $asset->purchase_price ?? '') }}">
            @error('purchase_price')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="purchase_date">Purchase Date</label>
            <input type="date" name="purchase_date" id="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date', optional($asset?->purchase_date)->format('Y-m-d')) }}">
            @error('purchase_date')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="warranty_until">Warranty Until</label>
            <input type="date" name="warranty_until" id="warranty_until" class="form-control" value="{{ old('warranty_until', optional($asset?->warranty_until)->format('Y-m-d')) }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="expire_date">Expire Date</label>
            <input type="date" name="expire_date" id="expire_date" class="form-control" value="{{ old('expire_date', optional($asset?->expire_date)->format('Y-m-d')) }}">
        </div>
    </div>
</div>

<div class="form-group">
    <label for="place_of_purchase">Place of Purchase</label>
    <input type="text" name="place_of_purchase" id="place_of_purchase" class="form-control" value="{{ old('place_of_purchase', $asset->place_of_purchase ?? '') }}">
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="supplier_name">Supplier Name</label>
            <input type="text" name="supplier_name" id="supplier_name" class="form-control" value="{{ old('supplier_name', $asset->supplier_name ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="supplier_address">Supplier Address</label>
            <input type="text" name="supplier_address" id="supplier_address" class="form-control" value="{{ old('supplier_address', $asset->supplier_address ?? '') }}">
        </div>
    </div>
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $asset->description ?? '') }}</textarea>
</div>

<div class="form-group">
    <label for="status">Status</label>
    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
        <option value="active" {{ old('status', $asset->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="disposed" {{ old('status', $asset->status ?? '') === 'disposed' ? 'selected' : '' }}>Disposed</option>
        <option value="lost" {{ old('status', $asset->status ?? '') === 'lost' ? 'selected' : '' }}>Lost</option>
    </select>
    @error('status')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="invoice_file">Invoice File</label>
    <input type="file" name="invoice_file" id="invoice_file" class="form-control-file @error('invoice_file') is-invalid @enderror">
    @error('invoice_file')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
    @if (!empty($asset?->invoice_file))
        <div class="mt-1"><a href="{{ asset('storage/'.$asset->invoice_file) }}" target="_blank">View current invoice</a></div>
    @endif
</div>
