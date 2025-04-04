<?php

namespace App\Http\Requests;

use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;

abstract class BaseRelationshipRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasRequestFormat;

    /**
     * Lấy danh sách các attribute cần chuyển đổi
     *
     * @return array
     */
    abstract protected function getAttributeNames(): array;

    /**
     * Lấy các quy tắc cho relationships
     *
     * @return array
     */
    abstract protected function getRelationshipRules(): array;

    /**
     * Lấy đối tượng ValidationMessages để tạo thông báo lỗi
     *
     * @return string
     */
    abstract protected function getValidationMessagesClass(): string;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation(): void
    {
        // Chuyển đổi từ direct format sang JSON:API format
        // Luôn đánh dấu là có relationships để đảm bảo chỉ chấp nhận JSON:API format đúng
        $this->convertToJsonApiFormat($this->getAttributeNames(), true);
    }

    /**
     * Lấy rules cho attributes
     *
     * @return array
     */
    abstract protected function getAttributeRules(): array;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $attributesRules = $this->mapAttributesRules($this->getAttributeRules());
        $relationshipsRules = $this->getRelationshipRules();

        return array_merge($attributesRules, $relationshipsRules);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        $messagesClass = $this->getValidationMessagesClass();
        $standardMessages = $messagesClass::getJsonApiMessages();

        $relationships = [];
        $relationshipRules = $this->getRelationshipRules();

        // Trích xuất tên và rules của mỗi relationship
        foreach ($relationshipRules as $path => $rules) {
            // Trích xuất tên relationship từ đường dẫn
            if (preg_match('/data\.relationships\.([^\.]+)/', $path, $matches)) {
                $relationName = $matches[1];
                $relationRules = [];

                // Xử lý từng rule, loại bỏ parameters
                foreach ($rules as $rule) {
                    if (is_string($rule)) {
                        // Lấy tên rule (phần trước dấu :)
                        $ruleName = (strpos($rule, ':') !== false)
                            ? substr($rule, 0, strpos($rule, ':'))
                            : $rule;

                        $relationRules[] = $ruleName;
                    }
                }

                $relationships[$relationName] = $relationRules;
            }
        }

        // Gọi phương thức từ enum class để tạo thông báo lỗi
        $relationshipMessages = $messagesClass::getRelationshipMessages($relationships);

        return array_merge($standardMessages, $relationshipMessages);
    }
}
