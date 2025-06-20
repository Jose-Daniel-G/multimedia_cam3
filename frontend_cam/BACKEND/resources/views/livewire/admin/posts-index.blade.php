@if (session('success'))
<div class="alert alert-success">
    <strong>{{ session('success') }}</strong>
</div>
@endif
<div class="card">
     @if ($posts->count())
        <div class="card-header">
            <input wire:model="search" type="text" name="name" id="name" placeholder="Ingrese el nombre del post"
                class="form-control">
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th colspan="2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr>
                            <td>{{ $post->id }}</td>
                            <td>{{ $post->name }}</td>
                            <td width="10px"><a href="{{ route('admin.posts.edit', $post) }}"
                                    class="btn btn-primary btn-sm">Editar</a></td>
                            <td width="10px">
                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST">
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit" name="delete"
                                        class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $posts->links() }}</div>
    @else
        <div class="card-body">
            <span class="text-danger">Inexistente</span>
        </div>
    @endif
</div>
