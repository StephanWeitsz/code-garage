@extends('layouts.app', ['title' => 'Submit Development Requirements'])

@section('content')
    <section class="stack">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Development request</p>
                <h1>Tell us what you want to build.</h1>
                <p class="hero-copy">
                    Share as much detail as you can. If something is still unclear, leave it blank and the admin team
                    can follow up by email or phone.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('development-requests.services.requirements.store') }}" class="panel stack">
            @csrf

            <div>
                <p class="eyebrow">Your details</p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label>
                        <span class="auth-label">Name</span>
                        <input type="text" name="client_name" class="auth-input" value="{{ old('client_name', auth()->user()?->name) }}" required>
                        @error('client_name') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="auth-label">Email</span>
                        <input type="email" name="client_email" class="auth-input" value="{{ old('client_email', auth()->user()?->email) }}" required>
                        @error('client_email') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="auth-label">Phone or WhatsApp</span>
                        <input type="text" name="client_phone" class="auth-input" value="{{ old('client_phone', auth()->user()?->mobile) }}">
                        @error('client_phone') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="auth-label">Company or organisation</span>
                        <input type="text" name="company_name" class="auth-input" value="{{ old('company_name') }}">
                        @error('company_name') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="auth-label">Preferred contact method</span>
                        <select name="preferred_contact_method" class="auth-input" required>
                            @foreach (['email' => 'Email', 'phone' => 'Phone', 'whatsapp' => 'WhatsApp'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('preferred_contact_method', 'email') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('preferred_contact_method') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>
                </div>
            </div>

            <div>
                <p class="eyebrow">Project shape</p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label>
                        <span class="auth-label">Project name</span>
                        <input type="text" name="project_name" class="auth-input" value="{{ old('project_name') }}" required>
                        @error('project_name') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="auth-label">Type of project</span>
                        <select name="project_type" class="auth-input" required>
                            @foreach (['Website', 'Client portal', 'Admin system', 'Automation', 'Dashboard/reporting', 'Integration', 'Learning platform extension', 'Other'] as $type)
                                <option value="{{ $type }}" @selected(old('project_type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('project_type') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>
                </div>

                <label class="mt-4 block">
                    <span class="auth-label">What problem should this solve?</span>
                    <textarea name="project_goal" class="auth-input" rows="5" required placeholder="Explain the goal, the business problem, or what should be easier after this exists.">{{ old('project_goal') }}</textarea>
                    @error('project_goal') <span class="auth-error">{{ $message }}</span> @enderror
                </label>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label>
                        <span class="auth-label">Who will use it?</span>
                        <textarea name="target_users" class="auth-input" rows="4" placeholder="Customers, staff, students, managers, suppliers...">{{ old('target_users') }}</textarea>
                        @error('target_users') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="auth-label">How do you handle this now?</span>
                        <textarea name="current_process" class="auth-input" rows="4" placeholder="Spreadsheets, email, manual steps, another system...">{{ old('current_process') }}</textarea>
                        @error('current_process') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>
                </div>
            </div>

            <div>
                <p class="eyebrow">Features</p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <span class="auth-label">Must-have features</span>
                        @for ($i = 0; $i < 5; $i++)
                            <input type="text" name="must_have_features[]" class="auth-input mt-2" value="{{ old("must_have_features.$i") }}" placeholder="Required feature {{ $i + 1 }}">
                        @endfor
                        @error('must_have_features') <span class="auth-error">{{ $message }}</span> @enderror
                        @error('must_have_features.*') <span class="auth-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <span class="auth-label">Nice-to-have features</span>
                        @for ($i = 0; $i < 5; $i++)
                            <input type="text" name="nice_to_have_features[]" class="auth-input mt-2" value="{{ old("nice_to_have_features.$i") }}" placeholder="Optional feature {{ $i + 1 }}">
                        @endfor
                        @error('nice_to_have_features') <span class="auth-error">{{ $message }}</span> @enderror
                        @error('nice_to_have_features.*') <span class="auth-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div>
                <p class="eyebrow">Practical details</p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label>
                        <span class="auth-label">Systems or integrations</span>
                        <textarea name="integrations" class="auth-input" rows="4" placeholder="Payment gateways, email, WhatsApp, accounting, existing websites, APIs...">{{ old('integrations') }}</textarea>
                        @error('integrations') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="auth-label">Content and data</span>
                        <textarea name="content_and_data" class="auth-input" rows="4" placeholder="Documents, images, products, users, spreadsheets, imported records...">{{ old('content_and_data') }}</textarea>
                        @error('content_and_data') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="auth-label">Timeline</span>
                        <select name="timeline" class="auth-input">
                            @foreach (['Not sure yet', 'As soon as possible', 'Within 1 month', '1 to 3 months', '3 months or more'] as $timeline)
                                <option value="{{ $timeline }}" @selected(old('timeline', 'Not sure yet') === $timeline)>{{ $timeline }}</option>
                            @endforeach
                        </select>
                        @error('timeline') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        <span class="auth-label">Budget range</span>
                        <select name="budget_range" class="auth-input">
                            @foreach (['Not sure yet', 'Under R5,000', 'R5,000 - R15,000', 'R15,000 - R50,000', 'R50,000+'] as $budgetRange)
                                <option value="{{ $budgetRange }}" @selected(old('budget_range', 'Not sure yet') === $budgetRange)>{{ $budgetRange }}</option>
                            @endforeach
                        </select>
                        @error('budget_range') <span class="auth-error">{{ $message }}</span> @enderror
                    </label>
                </div>

                <label class="mt-4 block">
                    <span class="auth-label">How will you know the project is successful?</span>
                    <textarea name="success_measure" class="auth-input" rows="4" placeholder="Fewer manual steps, more enquiries, easier reporting, faster student support...">{{ old('success_measure') }}</textarea>
                    @error('success_measure') <span class="auth-error">{{ $message }}</span> @enderror
                </label>

                <label class="mt-4 block">
                    <span class="auth-label">Anything else we should know?</span>
                    <textarea name="additional_context" class="auth-input" rows="5">{{ old('additional_context') }}</textarea>
                    @error('additional_context') <span class="auth-error">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="hero-actions">
                <button type="submit" class="button button-primary">Send request</button>
                <a href="{{ route('development-requests.services.index') }}" class="button button-secondary">Back to services</a>
            </div>
        </form>
    </section>
@endsection
