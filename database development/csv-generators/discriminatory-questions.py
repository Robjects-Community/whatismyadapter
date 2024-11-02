import pandas as pd

# Create the main question structure
questions_df = pd.DataFrame({
    'Question Number': ['Q1', 'Q1', 'Q1', 'Q1', 
                       'Q2', 'Q2', 'Q2',
                       'Q3', 'Q3', 'Q3', 'Q3',
                       'Q4', 'Q4', 'Q4', 'Q4', 'Q4', 'Q4', 'Q4', 'Q4',
                       'Q5', 'Q5', 'Q5', 'Q5'],
    'Question': ['What is your primary adapter type?', '', '', '',
                'What connection type do you need?', '', '',
                'What is your primary use case?', '', '', '',
                'What data transfer speed do you require?', '', '', '', '', '', '', '',
                'Do you need specific power delivery capabilities?', '', '', ''],
    'Options': ['USB-C', 'USB-A', 'Micro USB', 'Other',
               'Male-to-Male', 'Female-to-Female', 'Male-to-Female',
               'Charging', 'Data Transfer', 'Video Output (USB-C only)', 'Audio (USB-A only)',
               'USB 3.1 (USB-C)', 'USB 3.2 (USB-C)', 'USB4 (USB-C)', 
               'USB 2.0 (USB-A)', 'USB 3.0 (USB-A)', 'USB 3.1 (USB-A)',
               'USB 2.0 (Micro)', 'USB 3.0 (Micro)',
               'USB PD (USB-C)', 'None (USB-C)', 
               'Standard USB (USB-A/Micro)', 'Varies (Other)'],
    'Dependencies': ['None', 'None', 'None', 'None',
                    'Q1 != Other', 'Q1 != Other', 'Q1 != Other',
                    'All', 'All', 'USB-C only', 'USB-A only',
                    'If USB-C', 'If USB-C', 'If USB-C',
                    'If USB-A', 'If USB-A', 'If USB-A',
                    'If Micro USB', 'If Micro USB',
                    'If USB-C', 'If USB-C',
                    'If USB-A or Micro USB', 'If Other']
})

# Add skip logic notes
skip_logic = pd.DataFrame({
    'Skip Logic': [
        'If Q1 = Other, skip to Q5',
        'If Q1 = USB-C, show only USB-C speeds in Q4',
        'If Q1 = USB-A, show only USB-A speeds in Q4',
        'If Q1 = Micro USB, show only Micro USB speeds in Q4',
        'Video Output only shown for USB-C in Q3',
        'Audio only shown for USB-A in Q3'
    ]
})

# Create validation rules
validation_rules = pd.DataFrame({
    'Question': ['Q1', 'Q2', 'Q3', 'Q4', 'Q5'],
    'Validation': [
        'Single select required',
        'Single select required if Q1 != Other',
        'Single select required',
        'Single select required, show only relevant speeds based on Q1',
        'Single select required, options based on Q1'
    ]
})

# Display the DataFrames
print("Questions and Options:")
print(questions_df)
print("\nSkip Logic:")
print(skip_logic)
print("\nValidation Rules:")
print(validation_rules)