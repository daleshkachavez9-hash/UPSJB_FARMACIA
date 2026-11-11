<div class="mb-3">
    <label for="name" class="form-label">Nombre de la categoría</label>
    <input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}"
           @class(['form-control', 'is-invalid' => $errors->has('name')])>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Descripción</label>
    <textarea id="description" name="description" rows="3"
              @class(['form-control', 'is-invalid' => $errors->has('description')])
    >{{ old('description', $category->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
