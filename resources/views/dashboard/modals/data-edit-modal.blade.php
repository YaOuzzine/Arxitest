<x-code-editor-modal
    modalId="dataEditor"
    title="Edit Test Data"
    buttonIcon="save"
    buttonText="Save Changes"
    editorType="data"
    initialMode="json"
    type="data"
    x-on:save="saveEditedData">

    <!-- Usage Context field (extra field for data) -->
    <div>
        <label for="dataEditor-usage-context" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Usage Context <span class="text-red-500">*</span>
        </label>
        <input type="text" id="dataEditor-usage-context" x-model="formData.usage_context"
            class="form-input w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-zinc-600"
            placeholder="e.g., 'Valid input scenario' or 'Edge case testing'">
    </div>

    <!-- Sensitive Data Checkbox -->
    <div class="col-span-3 mt-2">
        <label class="flex items-center">
            <input type="checkbox" x-model="formData.is_sensitive" class="form-checkbox">
            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                Mark as sensitive data (contains private, personal, or confidential information)
            </span>
        </label>
    </div>
</x-code-editor-modal>
