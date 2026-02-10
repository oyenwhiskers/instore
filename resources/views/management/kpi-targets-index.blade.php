@extends('layouts.app')

@section('page_title', 'KPI Targets')
@section('page_desc', 'Define measurable targets for each promoter and reporting period.')

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table">
        <thead>
            <tr>
                <th>Promoter</th>
                <th>Email</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($promoters as $promoter)
                <tr>
                    <td>{{ $promoter->name }}</td>
                    <td>{{ $promoter->email }}</td>
                    <td><a href="{{ route('management.kpi-targets.edit', $promoter) }}" class="btn btn-ghost">Set Targets</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="muted">No promoters found.</td>
                </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>
@endsection
