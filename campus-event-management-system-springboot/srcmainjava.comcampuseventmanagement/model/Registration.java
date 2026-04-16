package com.campus.eventmanagement.model;

import jakarta.persistence.*;

@Entity
public class Registration {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    private String studentName;
    private String email;

    @ManyToOne
    @JoinColumn(name = "event_id")
    private Event event;

    // Getters & Setters
}
