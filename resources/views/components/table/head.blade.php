<!-- components/table/head.blade.php -->
@props(['sortable' => false])

<thead class="bg-zinc-50 dark:bg-zinc-800/80">
    <tr>
        {{ $slot }}
    </tr>
</thead>
