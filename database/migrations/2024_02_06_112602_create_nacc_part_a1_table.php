<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nacc_part_a1', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('institute_name',500)->nullable();
            $table->string('institute_head_name',500)->nullable();  
            $table->string('designation',500)->nullable();  
            $table->string('institute_func_campus',10)->nullable();
            $table->Integer('princ_phno')->nullable();
            $table->Integer('princ_alternate_phno')->nullable();            
            $table->Integer('princ_mobile')->nullable();   
            $table->string('princ_reg_email',500)->nullable();                                              
            $table->string('address',500)->nullable();     
            $table->string('city_town',255)->nullable();
            $table->string('state_ut',255)->nullable();
            $table->Integer('pin_code')->nullable();
            $table->date('confirm_autonomous_date')->nullable();
            $table->string('type_institute',100)->nullable();
            $table->string('location',100)->nullable();
            $table->string('financial_status',100)->nullable();
            $table->string('IQAC_director_name',500)->nullable();
            $table->Integer('phone_no')->nullable();
            $table->Integer('mobile_no')->nullable();
            $table->string('IQAC_email',500)->nullable();                                              
            $table->string('web_add_link_AQAR',500)->nullable();                                              
            $table->string('academic_calendar',10)->nullable();                                              
            $table->string('institute_weblink',500)->nullable();                                              
            $table->text('accrediation_details')->nullable();                                              
            $table->date('IQAC_establish_date')->nullable();       
            $table->text('institute_assurance')->nullable();
            $table->text('special_conferred_status')->nullable();  
            $table->string('IQAC_composition',10)->nullable();     
            $table->string('composition_file',255)->nullable();
            $table->integer('no_IQAC_meeting')->nullable();  
            $table->string('minutes_IQAC_meeting',20)->nullable(); 
            $table->string('uploaded_minutes',255)->nullable();
            $table->string('IQAC_recive_fund',10)->nullable();
            $table->integer('fund_amt')->nullable();
            $table->year('fund_year')->nullable();   
            $table->text('IQAC_significant_contribution')->nullable();
            $table->string('contribution_file',255)->nullable();
            $table->text('action_chalked_out')->nullable();
            $table->string('action_chalked_out_file',255)->nullable();
            $table->string('AQAR_placed_statutory',20)->nullable();
            $table->string('statutory_name',255)->nullable();   
            $table->date('statutory_date')->nullable();
            $table->string('NAAC_or_other',20)->nullable();    
            $table->string('submitted_AISHE',20)->nullable();
            $table->year('year_submission')->nullable();  
            $table->date('date_submission')->nullable(); 
            $table->string('management_info',20)->nullable(); 
            $table->string('brief_desc',600)->nullable();     
            $table->bigInteger('sub_institute_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nacc_part_a1');
    }
};
